<?php

namespace App\Security;

use App\Entity\User;
use App\Enum\UserStatus;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {

    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isVerified()) {
            throw new CustomUserMessageAuthenticationException(
                'Veuillez vérifier votre adresse email.'
            );
        }

        if ($user->getStatus() === UserStatus::PENDING) {
            throw new CustomUserMessageAuthenticationException(
                'Votre compte est en attente de validation par le CN2E.'
            );
        }

        if ($user->getStatus() === UserStatus::REFUSED) {
            throw new CustomUserMessageAuthenticationException(
                'Votre demande d’adhésion a été refusée par le CN2E.'
            );
        }
    }
}