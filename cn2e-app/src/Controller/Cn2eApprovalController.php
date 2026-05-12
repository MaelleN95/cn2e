<?php

namespace App\Controller;

use App\Enum\UserStatus;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/* 
 * Ce controller est temporaire
 * Il s'agit d'un essai de switch de UserStatus en attendant le backoffice
 */
class Cn2eApprovalController extends AbstractController
{
    #[Route('/cn2e/validation/accepter/{token}', name: 'app_cn2e_accept')]
    public function accept(
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $user = $userRepository->findOneBy([
            'validationToken' => $token,
        ]);

        if (
            !$user ||
            !$user->getValidationTokenExpiresAt() ||
            $user->getValidationTokenExpiresAt() < new \DateTimeImmutable()
        ) {
            throw $this->createNotFoundException();
        }

        $user->setStatus(UserStatus::ACCEPTED);

        $user->setValidationToken(null);
        $user->setValidationTokenExpiresAt(null);

        // TODO:
        // attribuer les rôles automatiquement
        // lorsque le backoffice existera

        $entityManager->flush();

        return new Response('Utilisateur accepté.');
    }

    #[Route('/cn2e/validation/refuser/{token}', name: 'app_cn2e_refuse')]
    public function refuse(
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $user = $userRepository->findOneBy([
            'validationToken' => $token,
        ]);

        if (
            !$user ||
            !$user->getValidationTokenExpiresAt() ||
            $user->getValidationTokenExpiresAt() < new \DateTimeImmutable()
        ) {
            throw $this->createNotFoundException();
        }

        $user->setStatus(UserStatus::REFUSED);

        $user->setValidationToken(null);
        $user->setValidationTokenExpiresAt(null);

        // TODO:
        // remplacer ce refus direct
        // par un formulaire backoffice
        // avec message explicatif optionnel

        $entityManager->flush();

        return new Response('Utilisateur refusé.');
    }
}