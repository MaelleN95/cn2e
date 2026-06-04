<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\UserStatus;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserCreator
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function prepare(User $user): string
    {
        $plainpassword = bin2hex(random_bytes(10));

        $user->setPassword($this->hasher->hashPassword($user, $plainpassword));
        $user->setStatus(UserStatus::ACCEPTED);
        $user->setIsVerified(true);

        return $plainpassword;
    }
}
