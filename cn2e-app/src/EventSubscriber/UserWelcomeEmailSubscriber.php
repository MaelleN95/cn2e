<?php

namespace App\EventSubscriber;

use App\Event\UserCreatedEvent;
use App\Service\SiteInformationAccessor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class UserWelcomeEmailSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private SiteInformationAccessor $siteInformationAccessor,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            UserCreatedEvent::class => 'onUserCreated',
        ];
    }

    public function onUserCreated(UserCreatedEvent $event): void
    {
        $user = $event->getUser();
        $plainPassword = $event->getPlainPassword();

        $appUrl = rtrim($_ENV['APP_URL'] ?? $_ENV['DEFAULT_URI'] ?? 'https://cn2e.fr', '/');

        $from = new Address($this->siteInformationAccessor->getSenderEmail(), $this->siteInformationAccessor->getShortName());

        $welcomeEmail = (new TemplatedEmail())
            ->from($from)
            ->to($user->getEmail())
            ->subject(sprintf('Bienvenue au %s', $this->siteInformationAccessor->getShortName()))
            ->htmlTemplate('emails/user_created.html.twig')
            ->context([
                'user' => $user,
                'plainPassword' => $plainPassword,
                'websiteUrl' => $appUrl,
                'loginUrl' => $appUrl . '/connexion',
            ]);

        $this->mailer->send($welcomeEmail);
    }
}
