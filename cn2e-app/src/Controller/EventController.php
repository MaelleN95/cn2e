<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/agenda', name: 'app_event')]
    public function index(Request $request, EventRepository $repo): Response
    {
        $allowedTabs = ['a-venir', 'passes'];

        $tab = $request->query->get('tab', 'a-venir');

        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'a-venir';
        }

        $events = $tab === 'passes'
            ? $repo->findPast()
            : $repo->findUpcoming();

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'activeTab' => $tab,
        ]);
    }

    #[Route('/agenda/{slug}', name: 'app_event_show')]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Event $event, EventRepository $repo): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
            'isPast' => $repo->isPast($event)
        ]);
    }
}
