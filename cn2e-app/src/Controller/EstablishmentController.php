<?php

namespace App\Controller;

use App\Repository\EstablishmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EstablishmentController extends AbstractController
{
    #[Route('/etablissements', name: 'app_establishment')]
    public function index(Request $request, EstablishmentRepository $repository): Response
    {
        $search = $request->query->get('q');

        if ($search) {
            $establishments = $repository->search($search);
        } else {
            $establishments = $repository->findAll();
        }

        $parameters = [
            'establishments' => $establishments,
            'search' => $search,
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->render('establishment/_results.html.twig', $parameters);
        }

        return $this->render('establishment/index.html.twig', $parameters);
    }

    #[Route('/etablissements/{slug}', name: 'app_establishment_show')]
    public function show(string $slug, EstablishmentRepository $repository): Response
    {
        $establishment = $repository->findOneBy(['slug' => $slug]);

        if (!$establishment) {
            throw $this->createNotFoundException('Établissement non trouvé');
        }

        return $this->render('establishment/show.html.twig', [
            'establishment' => $establishment,
        ]);
    }
}
