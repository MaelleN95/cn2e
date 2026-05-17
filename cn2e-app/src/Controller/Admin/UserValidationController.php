<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\UserStatus;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CN2E_ADMIN')]
class UserValidationController extends AbstractController
{
    #[AdminRoute(
        path: '/users/validation',
        name: 'user_validation'
    )]
    public function index(
        Request $request,
        UserRepository $repo
    ): Response {

        $query = $request->query->get('query');

        return $this->render('admin/user_validation/index.html.twig', [
            'pendingUsers' => $repo->findByStatusAndName(UserStatus::PENDING, $query),
            'acceptedUsers' => $repo->findByStatusAndName(UserStatus::ACCEPTED, $query),
            'refusedUsers' => $repo->findByStatusAndName(UserStatus::REFUSED, $query),
            'query' => $query,
        ]);
    }

    #[AdminRoute(
        path: '/users/{id}/status/{status}',
        name: 'user_change_status',
        options: [
            'requirements' => [
                'status' => 'pending|accepted|refused'
            ]
        ]
    )]
    public function changeStatus(
        User $user,
        UserStatus $status,
        EntityManagerInterface $em
    ): Response {

        $user->setStatus($status);

        $em->flush();

        $statusLabel = match ($status) {
            UserStatus::ACCEPTED => 'accepté(e)',
            UserStatus::REFUSED => 'refusé(e)',
            UserStatus::PENDING => 'en attente',
        };

        $this->addFlash(
            'success',
            sprintf(
                '%s est maintenant %s. Configurez ces droits d\'accès !',
                $user->getFullName(),
                $statusLabel
            )
        );

        if ($status === UserStatus::ACCEPTED) {
            return $this->redirectToRoute('admin_user_edit', [
                'entityId' => $user->getId(),
            ]);
        }

        return $this->redirectToRoute('admin_user_validation');
    }

    #[AdminRoute(
        path: '/users/validation/{id}',
        name: 'user_validation_show'
    )]
    public function show(User $user): Response
    {
        return $this->render('admin/user_validation/show.html.twig', [
            'user' => $user,
        ]);
    }
}