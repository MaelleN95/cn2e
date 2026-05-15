<?php

namespace App\Controller\Api;

use App\Repository\EstablishmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class EstablishmentMapController extends AbstractController
{
    #[Route('/api/map/establishments', name: 'api_map_establishments')]
    public function __invoke(
        EstablishmentRepository $repository,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $establishments = $repository->findAll();

        $data = array_map(static function ($establishment) use ($urlGenerator) {
            return [
                'name' => $establishment->getName(),
                'city' => $establishment->getCity(),
                'region' => $establishment->getRegion(),
                'latitude' => $establishment->getLatitude(),
                'longitude' => $establishment->getLongitude(),
                'url' => $urlGenerator->generate(
                    'app_establishment_show',
                    ['slug' => $establishment->getSlug()]
                ),
            ];
        }, $establishments);

        return $this->json($data);
    }
}