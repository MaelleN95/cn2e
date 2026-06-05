<?php

namespace App\EventSubscriber;

use App\Event\UserCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserCreatedAdminNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
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
        $creator = $event->getCreator();

        $adminUserIndexUrl = $this->urlGenerator->generate(
            'admin_user_index',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $adminUserEditUrl = null;
        if ($user->getId()) {
            try {
                $adminUserEditUrl = $this->urlGenerator->generate(
                    'admin_user_edit',
                    ['entityId' => $user->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            } catch (\Throwable) {
                $adminUserEditUrl = null;
            }
        }

        $from = new Address($_ENV['CONTACT_FROM'], 'CN2E');

        $context = [
            'admin' => $creator,
            'createdUser' => $user,
            'createdUserAdminUrl' => $adminUserEditUrl,
            'adminUserIndexUrl' => $adminUserIndexUrl,
        ];

        if ($creator && $creator->getEmail()) {
            $adminEmail = (new TemplatedEmail())
                ->from($from)
                ->to($creator->getEmail())
                ->addTo(new Address($_ENV['CONTACT_TO']))
                ->subject('Confirmation de création d’un utilisateur')
                ->htmlTemplate('emails/admin_user_created.html.twig')
                ->context($context);

            $this->mailer->send($adminEmail);
        } elseif (!empty($_ENV['CONTACT_TO'])) {
            $notify = (new TemplatedEmail())
                ->from($from)
                ->to(new Address($_ENV['CONTACT_TO']))
                ->subject('Confirmation de création d’un utilisateur')
                ->htmlTemplate('emails/admin_user_created.html.twig')
                ->context($context);

            $this->mailer->send($notify);
        }
    }
}
