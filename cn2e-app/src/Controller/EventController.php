<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class EventController extends AbstractController
{
    #[Route('/agenda', name: 'app_event')]
    public function index(Request $request, EventRepository $repo): Response
    {

        $isMember = false;
        $user = $this->getUser();

        if ($user instanceof UserInterface && method_exists($user, 'isCn2eMember')) {
            $isMember = $user->isCn2eMember();
        }

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
            'isMember' => $isMember
        ]);
    }

    #[Route('/agenda/{slug}', name: 'app_event_show')]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Event $event, EventRepository $repo): Response
    {

        if ($event->isMembersOnly() && !$this->isGranted('ROLE_CN2E_MEMBER')) {
            return $this->render('event/access_denied_event.html.twig', [
                'event' => $event,
            ]);
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
            'isPast' => $repo->isPast($event)
        ]);
    }
}
