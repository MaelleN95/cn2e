<?php

namespace App\Command;

use App\Entity\AcademicProgram;
use App\Repository\AcademicProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:maintenance:deduplicate-programs')]
class DeduplicateAcademicProgramsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private AcademicProgramRepository $programRepo,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simule la fusion sans ecrire en base.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = (bool) $input->getOption('dry-run');

        /** @var AcademicProgram[] $programs */
        $programs = $this->programRepo->findAll();

        usort(
            $programs,
            static fn (AcademicProgram $a, AcademicProgram $b): int => ($a->getId() ?? 0) <=> ($b->getId() ?? 0)
        );

        $canonicalByKey = [];
        $duplicateCount = 0;
        $mergedLinks = 0;
        $removedLinks = 0;

        foreach ($programs as $program) {
            $key = $this->buildProgramKey($program);

            if (!isset($canonicalByKey[$key])) {
                $canonicalByKey[$key] = $program;
                continue;
            }

            $duplicateCount++;
            $canonical = $canonicalByKey[$key];

            foreach ($program->getEstablishments()->toArray() as $establishment) {
                $hasCanonical = $establishment->getAcademicPrograms()->contains($canonical);

                if (!$hasCanonical) {
                    $mergedLinks++;

                    if (!$dryRun) {
                        $establishment->addAcademicProgram($canonical);
                    }
                }

                $removedLinks++;

                if (!$dryRun) {
                    $establishment->removeAcademicProgram($program);
                }
            }

            if (!$dryRun) {
                $this->em->remove($program);
            }
        }

        if (!$dryRun) {
            $this->em->flush();
        }

        $output->writeln($dryRun
            ? '<comment>Mode dry-run: aucune ecriture en base.</comment>'
            : '<info>Nettoyage execute en base.</info>'
        );
        $output->writeln('Formations total: ' . count($programs));
        $output->writeln('Groupes uniques: ' . count($canonicalByKey));
        $output->writeln('Doublons detectes: ' . $duplicateCount);
        $output->writeln('Liaisons ajoutees vers canonique: ' . $mergedLinks);
        $output->writeln('Liaisons supprimees sur doublons: ' . $removedLinks);

        return Command::SUCCESS;
    }

    private function buildProgramKey(AcademicProgram $program): string
    {
        $level = $this->normalizeForCompare($program->getLevel() ?? '');
        $title = $this->normalizeForCompare($program->getTitle() ?? '');

        return $level . '|' . $title;
    }

    private function normalizeForCompare(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        if ($ascii !== false) {
            $value = $ascii;
        }

        return preg_replace('/[^a-z0-9]+/', '', $value) ?? $value;
    }
}
