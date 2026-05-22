<?php

namespace App\Command;

use App\Entity\AcademicProgram;
use App\Entity\Establishment;
use App\Repository\AcademicProgramRepository;
use App\Repository\EstablishmentRepository;
use App\Service\EstablishmentGeocoder;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Csv\Writer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsCommand(name: 'app:import:establishments')]
class ImportEstablishmentsCommand extends Command
{
    private array $errors = [];
    private array $infos = [];

    public function __construct(
        private EntityManagerInterface $em,
        private EstablishmentRepository $establishmentRepo,
        private AcademicProgramRepository $programRepo,
        private EstablishmentGeocoder $geocoder,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');

        $csv = Reader::createFromPath($file);
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);

        $i = 0;

        foreach ($csv as $row) {
            $i++;

            try {
                $this->processRow($row, $output);
            } catch (\Throwable $e) {
                $this->errors[] = [
                    'row' => $i,
                    'name' => $row['Nom'] ?? null,
                    'error' => $e->getMessage()
                ];
            }

            if ($i % 50 === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();

        $this->generateErrorReport();

        $output->writeln('Import terminé');
        $output->writeln('Erreurs: ' . count($this->errors));
        $output->writeln('Infos: ' . count($this->infos));

        return Command::SUCCESS;
    }

    private function processRow(array $row, OutputInterface $output): void
    {
        $slugger = new AsciiSlugger();

        $rawName = trim($row['Nom'] ?? '');
        $etabType = trim($row['Etablissement'] ?? '');

        $name = trim($etabType . ' ' . $rawName);

        if (empty($rawName) && !empty($city)) {
            $name = trim($etabType . ' ' . $city);

            $this->infos[] = [
                'row' => $name,
                'field' => 'Nom',
                'message' => 'Nom absent, ville utilisée dans le nom'
            ];
        }

        $email = $row['adresse mail'] ?? null;
        $website = $row['URL'] ?? null;
        $city = $row['Ville'] ?? null;
        $street = trim($row['Adresse'] ?? '');
        $postalCode = trim($row['Code postal'] ?? '');
        $city = trim($row['Ville'] ?? '') ?: null;

        $parts = array_filter([
            $street,
            $postalCode,
            $city
        ]);

        $address = !empty($parts)
            ? implode(' ', $parts)
            : null;

        if (empty($address) && !empty($city)) {
            $address = $city;

            $this->infos[] = [
                'row' => $name,
                'field' => 'Adresse',
                'message' => 'Adresse absente, ville utilisée comme adresse'
            ];
        }

        $phone = $row['Téléphone'] ?? null;

        if ($phone) {
            $phone = trim($phone);
            $phone = preg_replace('/\s+/', '', $phone);
        } else {
            $this->infos[] = [
                'row' => $name,
                'field' => 'Téléphone',
                'message' => 'Champ vide'
            ];
        }

        if (empty($city)) {
            $this->infos[] = [
                'row' => $name,
                'field' => 'Ville',
                'message' => 'Champ vide'
            ];
        }

        $slug = $slugger->slug($name)->lower();

        $addressHash = md5($address ?? '');

        $establishment = $this->findExisting($slug, $email, $addressHash);

        if (!$establishment) {
            $establishment = new Establishment();
        }
        
        $establishment->setName($name);
        $establishment->setEmail($email);
        $establishment->setWebsite($website);
        $establishment->setAddress($address);
        $establishment->setCity($city);

        $establishment->setPhone($phone);

        $this->geocoder->hydrate($establishment);

        $this->infos[] = [
            'row' => $name,
            'import_address' => $address,
            'final_address' => implode(' ', array_filter([
                $establishment->getAddress(),
                $establishment->getDepartment(),
                $establishment->getCity(),
            ])),
        ];

        $this->em->persist($establishment);
        

        // PROGRAMS
        $this->syncPrograms($establishment, $row);
    }

    private function findExisting(?string $slug, ?string $email, ?string $addressHash): ?Establishment
    {
        if ($email) {
            $existing = $this->establishmentRepo->findOneBy(['email' => $email]);
            if ($existing) {
                return $existing;
            }
        }

        if ($addressHash) {
            $existing = $this->establishmentRepo->findOneBy(['addressHash' => $addressHash]);
            if ($existing) {
                return $existing;
            }
        }

        return $this->establishmentRepo->findOneBy(['slug' => $slug]);
    }

    private function syncPrograms(Establishment $establishment, array $row): void
    {
        foreach ($row as $column => $value) {

            if (empty($value)) {
                continue;
            }

            $value = trim($value);

            if (str_starts_with($column, 'CAP')) {
                $this->attachProgram($establishment, 'CAP', $value);
            }

            if (str_starts_with($column, 'post-CAP')) {
                $this->attachProgram($establishment, 'Post CAP', $value);
            }

            if ($column === 'Apprentissage') {
                $this->attachProgram($establishment, 'Apprentissage', $value);
            }
        }
    }

    private function attachProgram(Establishment $establishment, string $level, string $title): void
    {
        $title = trim($title);
        $title = preg_replace('/\s+/', ' ', $title);

        $program = $this->programRepo->findOneBy([
            'level' => $level,
            'title' => $title
        ]);

        if (!$program) {
            $program = new AcademicProgram();
            $program->setLevel($level);
            $program->setTitle($title);

            $this->em->persist($program);
        }

        $establishment->addAcademicProgram($program);
    }

    private function generateErrorReport(): void
    {
        $writer = Writer::createFromPath('var/import_establishments_errors.csv', 'w+');

        $writer->insertOne([
            'Establishment',
            'Import Address',
            'Final Address',
            'Error',
            'Info'
        ]);

        foreach ($this->errors as $error) {
            $writer->insertOne([
                $error['name'],
                '',
                '',
                $error['error'],
                ''
            ]);
        }

        foreach ($this->infos as $info) {
            $writer->insertOne([
                $info['row'] ?? '',
                $info['import_address'] ?? '',
                $info['final_address'] ?? '',
                '',
                isset($info['field'])
                    ? $info['field'] . ' : ' . $info['message']
                    : ''
            ]);
        }
    }
}