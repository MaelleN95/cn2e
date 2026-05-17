<?php

namespace App\EventSubscriber;

use App\Entity\AcademicProgram;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Bundle\SecurityBundle\Security;

class AcademicProgramFormSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    public function onPreSetData(FormEvent $formEvent): void
    {
        $academicProgram = $formEvent->getData();
        $form = $formEvent->getForm();

        $user = $this->security->getUser();

        if (!$academicProgram instanceof AcademicProgram || !$user instanceof User) {
            return;
        }

        $roles = $user->getRoles();
        
        $isSuperAdmin = in_array('ROLE_SUPER_ADMIN', $roles, true);

        $userEstablishment = $user->getEstablishment();

        $establishments = $academicProgram->getEstablishments();

        $userIsLinkedToProgram = $establishments->contains($userEstablishment);

        $countEstablishments = count($establishments) === 1;

        $inputIsDisabled = true;

        if (($userIsLinkedToProgram && $countEstablishments) || $isSuperAdmin) {
            $inputIsDisabled = false;
        }

        if ($form->has('level')) {
            $form->add('level', null, [
                'label' => 'admin.academicprogram.level',
                'disabled' => $inputIsDisabled,
                'help' => $inputIsDisabled
                ? 'Vous ne pouvez modifier ce champ que si cette formation est liée uniquement à votre établissement.'
                : null,
            ]);
        }

        if ($form->has('title')) {
            $form->add('title', null, [
                'label' => 'admin.academicprogram.title',
                'disabled' => $inputIsDisabled,
                'help' => $inputIsDisabled
                ? 'Vous ne pouvez modifier ce champ que si cette formation est liée uniquement à votre établissement.'
                : null,
            ]);
        }
    }

    public function onPostSubmit(FormEvent $formEvent): void
    {
        $academicProgram = $formEvent->getData();
        $form = $formEvent->getForm();

        $user = $this->security->getUser();

        if (!$academicProgram instanceof AcademicProgram || !$user instanceof User) {
            return;
        }

        $userEstablishment = $user->getEstablishment();

        $establishments = $academicProgram->getEstablishments();

        $userIsLinkedToProgram = $establishments->contains($userEstablishment);

        $countEstablishments = count($establishments) === 1;

        $roles = $user->getRoles();

        $isLocalAdmin = in_array('ROLE_LOCAL_ADMIN', $roles, true);
        $isCn2eAdmin = in_array('ROLE_CN2E_ADMIN', $roles, true);
        $isSuperAdmin = in_array('ROLE_SUPER_ADMIN', $roles, true);

        $isAllowedRole = $isLocalAdmin || $isCn2eAdmin || $isSuperAdmin;

        if (!$isAllowedRole) {
            $form->addError(new FormError('Accès refusé'));
            return;
        }

        $canModify = ($userIsLinkedToProgram && $countEstablishments) || $isSuperAdmin;

        if (!$canModify) {
            $form->addError(new FormError('Vous ne pouvez pas modifier cette formation pour plusieurs établissements.'));
        }
    }
}