<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class HasCn2eRole extends Constraint
{
    public string $message = 'Un membre de l\'organisation doit obligatoirement avoir un rôle au sein de l\'équipe';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}