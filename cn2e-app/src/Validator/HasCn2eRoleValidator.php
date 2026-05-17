<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasCn2eRoleValidator extends ConstraintValidator
{
    public function __construct(
        private Security $security
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {

        if (!$constraint instanceof HasCn2eRole) {
            throw new UnexpectedTypeException($constraint, HasCn2eRole::class);
        }

        if (!$value instanceof User) {
            return;
        }

        $roles = $value->getRoles();

        $isCn2eMember = array_intersect($roles, [
            'ROLE_CN2E_MEMBER',
            'ROLE_CN2E_ADMIN',
            'ROLE_SUPER_ADMIN',
        ]);

        if (empty($isCn2eMember)) {
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