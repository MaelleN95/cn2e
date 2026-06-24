<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

final class AdminAccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        if (!str_starts_with($request->getPathInfo(), '/admin')) {
            return null;
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return null;
        }

        // getPrimaryRole() is ROLE_USER when no business role is assigned.
        if ($user->getPrimaryRole() !== 'ROLE_USER') {
            return null;
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }
}
