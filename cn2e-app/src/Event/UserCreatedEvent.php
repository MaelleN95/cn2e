<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

final class UserCreatedEvent extends Event
{
    public function __construct(
        private User $user,
        private string $plainPassword,
        private ?User $creator = null
    ) {}

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }
}
