<?php

namespace App\Controller;

use App\Params\Event\MergeRequestOpened;
use App\Params\Gitlab\MergeRequestEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RootController extends AbstractController
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    #[Route('/', name: 'app_root')]
    public function index(Request $request): Response
    {
        $data = $request->toArray();

        if (empty($data)) {
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        $mergeRequestEvent = MergeRequestEvent::fromJson($data);

        // Handle webhook events
        if ($mergeRequestEvent->object_attributes->action === 'open') {
            $this->eventDispatcher->dispatch(
                MergeRequestOpened::fromEvent($mergeRequestEvent)
            );
        }

        return new Response();
    }
}
