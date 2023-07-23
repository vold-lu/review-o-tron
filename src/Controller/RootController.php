<?php

namespace App\Controller;

use App\Service\MergeRequestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RootController extends AbstractController
{
    public function __construct(private readonly MergeRequestHandler $mergeRequestHandler)
    {
    }

    #[Route('/', name: 'app_root')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/RootController.php',
        ]);
    }
}
