<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasCn2eRoleValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof HasCn2eRole) {
            throw new UnexpectedTypeException($constraint, HasCn2eRole::class);
        }

        if (!$value instanceof User) {
            return;
        }

        if (!in_array('ROLE_CN2E_MEMBER', $value->getRoles(), true)) {
            return;
        }

        $cn2erole = $value->getCn2eRole();

        if (!$cn2erole) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('cn2eRole')
                ->addViolation();
        }
    }
}