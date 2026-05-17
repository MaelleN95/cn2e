<?php

namespace App\Enum;

enum UserStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REFUSED = 'refused';

    public function canBeAccepted(): bool
    {
        return $this !== self::ACCEPTED;
    }

    public function canBeRefused(): bool
    {
        return $this !== self::REFUSED;
    }
}