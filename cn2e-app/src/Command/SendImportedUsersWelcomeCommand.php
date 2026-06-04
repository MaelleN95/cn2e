<?php

namespace App\Command;

use App\Repository\UserRepository;
use League\Csv\Reader;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsCommand(name: 'app:send-imported-users-welcome')]
class SendImportedUsersWelcomeCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED)
            ->addArgument('excludeEmail', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');
        $excludeEmail = strtolower(trim($input->getArgument('excludeEmail') ?? ''));

        $csv = Reader::createFromPath($file);
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);

        $sent = 0;
        $skipped = 0;

        foreach ($csv as $row) {

            $level = strtolower(trim($row['Niveau d\\alerte'] ?? ''));
            $email = strtolower(trim($row['Email'] ?? ''));
            $identity = trim($row['Identité'] ?? '');
            $message = trim($row['Message'] ?? '');

            if ($level !== 'info') {
                continue;
            }

            if (!str_contains($message, 'password:')) {
                continue;
            }

            if (empty($email)) {
                $skipped++;
                $output->writeln("SKIP - email vide - {$identity}");
                continue;
            }

            if ($excludeEmail && $email === $excludeEmail) {
                $skipped++;
                $output->writeln("SKIP - email exclu - {$email}");
                continue;
            }

            preg_match('/password:\s(.+)$/', $message, $matches);

            $plainPassword = $matches[1] ?? null;

            if (!$plainPassword) {
                $skipped++;
                $output->writeln("SKIP - mot de passe introuvable - {$email}");
                continue;
            }

            $user = $this->userRepository->findOneBy([
                'email' => $email
            ]);

            if (!$user) {
                $skipped++;
                $output->writeln("SKIP - utilisateur absent en base - {$email}");
                continue;
            }

            $appUrl = rtrim($_ENV['APP_URL'] ?? $_ENV['DEFAULT_URI'] ?? 'https://cn2e.fr', '/');

            $emailMessage = (new TemplatedEmail())
                ->from(new Address($_ENV['CONTACT_FROM'], 'CN2E'))
                ->to($email)
                ->subject('Bienvenue sur le nouveau site du CN2E')
                ->htmlTemplate('emails/user_welcome.html.twig')
                ->context([
                        'user' => $user,
                        'plainPassword' => $plainPassword,
                        'websiteUrl' => $appUrl,
                        'loginUrl' => $appUrl . '/connexion',
                    ]);

            $this->mailer->send($emailMessage);

            $sent++;

            $output->writeln("OK - {$email}");
        }

        $output->writeln('');
        $output->writeln("Emails envoyés : {$sent}");
        $output->writeln("Emails ignorés : {$skipped}");

        return Command::SUCCESS;
    }
}