<?php

namespace App\EventSubscriber;

use App\Security\FormSecurityGuard;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class FormSecuritySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FormSecurityGuard $securityGuard,
        private RequestStack $requestStack
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    public function onPostSubmit(FormEvent $event): void
    {
        $form = $event->getForm();

        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return;
        }

        try {
            $this->securityGuard->check($request, $form);
        } catch (AccessDeniedHttpException $e) {
            $form->addError(new FormError('Soumission refusée'));
        }
    }
}