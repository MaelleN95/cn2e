<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileFormType;
use App\Service\SiteInformationAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProfileController extends AbstractController
{
    public function __construct(
        private SiteInformationAccessor $siteInformationAccessor,
    ) {
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/mon-profil', name: 'app_profile', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        if (!$this->isGranted('ROLE_CN2E_MEMBER') && !$this->isGranted('ROLE_LOCAL_ADMIN')) {
            throw $this->createAccessDeniedException(sprintf('Cette page est réservée aux membres %s.', $this->siteInformationAccessor->getShortName()));
        }

        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Vos informations de profil ont été mises à jour.'
            );

            return $this->redirectToRoute('app_profile');
        }

    
        return $this->render('profile/index.html.twig', [
            'profileForm' => $form,
            'user' => $user,
        ]);
    }
}
