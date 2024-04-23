<?php

namespace App\Controller;

use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class MovieController extends AbstractController
{
    #[Route('/movies', name: 'app_movie')]
    public function index(MovieRepository $movieRepository): JsonResponse
    {
        $movies = $movieRepository->findAll();

        $movies2= [];
        foreach ($movies as $movie) {
            if(!empty($movie->getName())) {
                $movies2[] = [
                    'id' => $movie->getId(),
                    'nom' => $movie->getName(),
                    'description' => $movie->getDescription(),
                    'date de parution' => $movie->getReleaseAt(),
                    'note' => $movie->getRating(),
                ];
            }
        }

        return $this->json($movies2);
    }
}
