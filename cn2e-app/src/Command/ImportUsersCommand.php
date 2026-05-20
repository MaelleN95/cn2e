<?php

namespace App\Command;

use App\Entity\User;
use App\Enum\UserStatus;
use App\Repository\EstablishmentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Csv\Writer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;

#[AsCommand(name: 'app:import:users')]
class ImportUsersCommand extends Command
{
    private array $logs = [];

    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepo,
        private EstablishmentRepository $establishmentRepo,
        private UserPasswordHasherInterface $hasher,
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
                $this->processRow($row);
            } catch (\Throwable $e) {
                $this->logs[] = [
                    'row' => $i,
                    'email' => $row['email'] ?? null,
                    'level' => 'error',
                    'message' => $e->getMessage(),
                ];
            }

            if ($i % 50 === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->generateReport();

        $output->writeln('Import utilisateurs terminé');
        $output->writeln('Logs: ' . count($this->logs));

        return Command::SUCCESS;
    }

    private function processRow(array $row): void
    {
        $email = mb_strtolower(trim($row['email'] ?? ''));

        $firstName = ucfirst(mb_strtolower(trim($row['Prénom'] ?? '')));
        $lastName = ucfirst(mb_strtolower(trim($row['Nom'] ?? '')));

        $roleLabel = trim($row['cn2erole'] ?? '');
        $profession = trim($row['profession'] ?? '');
        $establishmentName = trim($row['établissement'] ?? '');

        $identity = $firstName . ' ' . $lastName;

        if (!$email) {
            $this->logs[] = [
                'row' => null,
                'email' => null,
                'identity' => $identity,
                'level' => 'error',
                'message' => 'Email manquant'
            ];
            return;
        }

        // Eviter les doublons
        $existing = $this->userRepo->findOneBy(['email' => $email]);
        if ($existing) {
            $this->logs[] = [
                'identity' => $identity,
                'row' => null,
                'email' => $email,
                'level' => 'info',
                'message' => 'Utilisateur déjà existant'
            ];
            return;
        }

        $establishment = null;
        if ($establishmentName) {
            $establishment = $this->establishmentRepo->findOneBy(['name' => $establishmentName]);

            if (!$establishment) {
                $this->logs[] = [
                    'identity' => $identity,
                    'row' => null,
                    'email' => $email,
                    'level' => 'warning',
                    'message' => "Établissement introuvable: $establishmentName"
                ];
            }
        } else {
            $this->logs[] = [
                'identity' => $identity,
                'row' => null,
                'email' => $email,
                'level' => 'warning',
                'message' => 'Établissement vide'
            ];
        }

        // password random 8 chars
        $plainPassword = ByteString::fromRandom(8)->toString();

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setProfession($profession);
        $user->setCn2eRole($roleLabel);

        $user->setRoles(['ROLE_CN2E_MEMBER']);
        $user->setStatus(UserStatus::ACCEPTED);
        $user->setIsVerified(true);

        $user->setEstablishment($establishment);

        $hashedPassword = $this->hasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->em->persist($user);

        $this->logs[] = [
            'identity' => $identity,
            'row' => null,
            'email' => $email,
            'level' => 'info',
            'message' => "Utilisateur créé | password: $plainPassword"
        ];
    }

    private function generateReport(): void
    {
        $writer = Writer::createFromPath('var/import_users_report.csv', 'w+');

        $writer->insertOne(['Identité', 'Row', 'Email', 'Niveau d\alerte', 'Message']);

        foreach ($this->logs as $log) {
            $writer->insertOne([
                $log['identity'] ?? '',
                $log['row'] ?? '',
                $log['email'] ?? '',
                $log['level'],
                $log['message'],
            ]);
        }
    }
}