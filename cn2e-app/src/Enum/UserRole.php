<?php

namespace App\Enum;

enum UserRole: string
{
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    case CN2E_ADMIN = 'ROLE_CN2E_ADMIN';
    case LOCAL_ADMIN = 'ROLE_LOCAL_ADMIN';
    case CN2E_MEMBER = 'ROLE_CN2E_MEMBER';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super administrateur',
            self::CN2E_ADMIN => 'Administrateur CN2E',
            self::LOCAL_ADMIN => 'Administrateur local',
            self::CN2E_MEMBER => 'Membre CN2E',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Accès total à la plateforme et gestion des administrateurs.',
            self::CN2E_ADMIN => 'Gestion des contenus, utilisateurs et validations CN2E.',
            self::LOCAL_ADMIN => 'Gestion locale de son établissement et formations affiliées.',
            self::CN2E_MEMBER => 'Accès aux contenus réservés aux adhérents.',
        };
    }

    public static function choicesForSuperAdmin(): array
    {
        return [
            self::SUPER_ADMIN->label() => self::SUPER_ADMIN->value,
            self::CN2E_ADMIN->label() => self::CN2E_ADMIN->value,
            self::CN2E_MEMBER->label() => self::CN2E_MEMBER->value,
        ];
    }

    public static function choicesForCn2eAdmin(): array
    {
        return [
            self::CN2E_MEMBER->label() => self::CN2E_MEMBER->value,
        ];
    }
}