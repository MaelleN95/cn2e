<?php

namespace App\EventSubscriber;

use App\Event\UserCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class UserCreatedAdminNotificationSubscriber implements EventSubscriberInterface
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
        $creator = $event->getCreator();

        $from = new Address($_ENV['CONTACT_FROM'], 'CN2E');

        if ($creator && $creator->getEmail()) {
            $adminEmail = (new TemplatedEmail())
                ->from($from)
                ->to($creator->getEmail())
                ->addTo(new Address($_ENV['CONTACT_TO']))
                ->subject('Confirmation de création d’un utilisateur')
                ->htmlTemplate('emails/admin_user_created.html.twig')
                ->context([
                    'admin' => $creator,
                    'createdUser' => $user,
                ]);

            $this->mailer->send($adminEmail);
        } elseif (!empty($_ENV['CONTACT_TO'])) {
            $notify = (new TemplatedEmail())
                ->from($from)
                ->to(new Address($_ENV['CONTACT_TO']))
                ->subject('Confirmation de création d’un utilisateur')
                ->htmlTemplate('emails/admin_user_created.html.twig')
                ->context([
                    'admin' => null,
                    'createdUser' => $user,
                ]);

            $this->mailer->send($notify);
        }
    }
}
