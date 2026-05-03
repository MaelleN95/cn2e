<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TeamController extends AbstractController
{
    #[Route('/l-equipe-du-cn2e', name: 'app_team')]
    public function index(UserRepository $userRepository): Response
    {
        $members = $userRepository->findCn2eMembers();

        return $this->render('team/team.html.twig', [
            'members' => $members,
        ]);
    }
}
