<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MovieController extends AbstractController
{
    private $_entityManager;
    private $_movieRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        MovieRepository $movieRepository
        )
    {
        $this->_entityManager = $entityManager;
        $this->_movieRepository = $movieRepository;
    }

    #[Route('/movies', name: 'app_movie')]
    public function listMovies(): JsonResponse
    {
        $movies = $this->_movieRepository->findAll();

        if (!$movies) {
            return new BadRequestException('Erreur sur la requete', Response::HTTP_BAD_REQUEST);
        }

        $moviesToJson = [];

        foreach ($movies as $movie) {
            $moviesToJson[] = [
                'id' => $movie->getId(),
                'nom' => $movie->getName(),
                'description' => $movie->getDescription(),
                'date de parution' => $movie->getReleaseAt(),
                'note' => $movie->getRating(),
            ];
        }

        return $this->json($moviesToJson, Response::HTTP_OK);
    }

    #[Route('/movie/{id}', name: 'app_movie_show')]
    public function showMovie(int $id): JsonResponse
    {
        $movie = $this->_movieRepository->findOneBy(['id' => $id]);

        if (!$movie) {
            return new BadRequestException('Erreur sur la requete', Response::HTTP_BAD_REQUEST);
        }

        $movieToJson = [
            'id' => $movie->getId(),
            'nom' => $movie->getName(),
            'description' => $movie->getDescription(),
            'date de parution' => $movie->getReleaseAt(),
            'note' => $movie->getRating(),
        ];

        return $this->json($movieToJson, Response::HTTP_OK);
    }

    #[Route('/movie/new', name: 'app_movie_new', methods: ['POST'])]
    public function createMovie(Request $request): Response
    {
        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData)) {
            return new BadRequestException('Erreur sur la requete', Response::HTTP_BAD_REQUEST);
        }

        $movie = new Movie();
        $movie->setName($requestData['name']);
        $movie->setDescription($requestData['description']);
        $movie->setRating($requestData['rating']);
 
        $this->_entityManager->persist($movie);
        $this->_entityManager->flush();

        return new Response('Film créé avec succès!', Response::HTTP_CREATED);
    }

    #[Route('/movie/edit/{id}', name: 'app_movie_edit', methods: ['PUT'])]
    public function updateMovie(Request $request, int $id): Response
    {
        $movie = $this->_movieRepository->findOneBy(['id' => $id]);

        if (!$movie) {
            return new Response('Film non trouvé', Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData)) {
            return new BadRequestException('Erreur sur la requete', Response::HTTP_BAD_REQUEST);
        }

        $movie->setName($requestData['name']);
        $movie->setDescription($requestData['description']);
        $movie->setRating($requestData['rating']);

        $this->_entityManager->flush();

        return new Response('Film mis à jour avec succès!', Response::HTTP_OK);
    }

    #[Route('/movie/{id}', name: 'app_movie_delete', methods: ['DELETE'])]
    public function deleteMovie(Request $request, int $id): Response
    {
        $movie = $this->_movieRepository->findOneBy(['id' => $id]);

        if (!$movie) {
            return new BadRequestException('Erreur sur la requete', Response::HTTP_BAD_REQUEST);
        }

        $this->_entityManager->remove($movie);
        $this->_entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }


}
