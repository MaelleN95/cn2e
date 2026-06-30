<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class PasswordResetMailer
{
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MailerInterface $mailer,
        private SiteInformationAccessor $siteInformationAccessor,
    ) {
    }

    /**
     * @throws ResetPasswordExceptionInterface
     */
    public function send(User $user): void
    {
        $resetToken = $this->resetPasswordHelper->generateResetToken($user);

        $email = (new TemplatedEmail())
            ->from(new Address($this->siteInformationAccessor->getSenderEmail(), $this->siteInformationAccessor->getShortName()))
            ->to((string) $user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('emails/reset_password_email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $this->mailer->send($email);
    }
}