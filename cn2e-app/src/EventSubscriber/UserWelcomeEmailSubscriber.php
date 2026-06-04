<?php

namespace App\EventSubscriber;

use App\Event\UserCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class UserWelcomeEmailSubscriber implements EventSubscriberInterface
{
    public function __construct(private MailerInterface $mailer) {}

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

        $from = new Address($_ENV['CONTACT_FROM'], 'CN2E');

        $welcomeEmail = (new TemplatedEmail())
            ->from($from)
            ->to($user->getEmail())
            ->subject('Bienvenue au CN2E')
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
