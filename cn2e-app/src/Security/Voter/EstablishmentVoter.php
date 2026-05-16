<?php

namespace App\Security\Voter;

use App\Entity\Establishment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EstablishmentVoter extends Voter
{
    const VIEW = 'ESTABLISHMENT_VIEW';
    const EDIT = 'ESTABLISHMENT_EDIT';
    const DELETE = 'ESTABLISHMENT_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Establishment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // SUPER_ADMIN override total
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        /** @var Establishment $establishment */
        $establishment = $subject;

        return match ($attribute) {

            self::VIEW => $this->canView($user, $establishment),
            self::EDIT => $this->canEdit($user, $establishment),
            self::DELETE => $this->canDelete(),

            default => false,
        };
    }

    private function canView(User $user, Establishment $establishment): bool
    {
        return
            in_array('ROLE_CN2E_ADMIN', $user->getRoles(), true)
            || $user->getEstablishment() === $establishment;
    }

    private function canEdit(User $user, Establishment $establishment): bool
    {
        // LOCAL ADMIN -> uniquement SON établissement
        if (in_array('ROLE_LOCAL_ADMIN', $user->getRoles(), true)) {
            return $user->getEstablishment() === $establishment;
        }

        // CN2E ADMIN -> tous les établissements
        return in_array('ROLE_CN2E_ADMIN', $user->getRoles(), true);
    }

    private function canDelete(): bool
    {
        return false; // sauf SUPER ADMIN géré plus haut
    }
}