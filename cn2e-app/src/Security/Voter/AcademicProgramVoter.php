<?php

namespace App\Security\Voter;

use App\Entity\AcademicProgram;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AcademicProgramVoter extends Voter
{
    public const DELETE = 'ACADEMIC_PROGRAM_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::DELETE
        ]);
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

        return match ($attribute) {
            self::DELETE => $this->canDelete($user, $subject),
            default => false,
        };
    }

    private function canDelete(User $user, AcademicProgram $program): bool
    {
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        $userEstablishment = $user->getEstablishment();

        $establishments = $program->getEstablishments();

        $isLinked = $establishments->contains($userEstablishment);
        $count = $establishments->count() === 1;

        return $isLinked && $count;
    }
}