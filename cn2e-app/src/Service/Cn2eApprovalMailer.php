<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Cn2eApprovalMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function send(User $user, ?string $requestMessage): void
    {
        $acceptUrl = $this->urlGenerator->generate(
            'app_cn2e_accept',
            [
                'token' => $user->getValidationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $refuseUrl = $this->urlGenerator->generate(
            'app_cn2e_refuse',
            [
                'token' => $user->getValidationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new TemplatedEmail())
            ->from(new Address($_ENV['CONTACT_FROM'], 'CN2E'))
            ->to(new Address($_ENV['CONTACT_TO']))
            ->subject('Nouvelle demande d’adhésion')
            ->htmlTemplate('emails/cn2e_user_validation.html.twig')
            ->context([
                'user' => $user,
                'requestMessage' => $requestMessage,
                'acceptUrl' => $acceptUrl,
                'refuseUrl' => $refuseUrl,
            ]);

        $this->mailer->send($email);
    }
}