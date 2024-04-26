<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MovieController extends AbstractController
{
    private $_entityManager;
    private $_movieRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        MovieRepository $movieRepository
    ) {
        $this->_entityManager = $entityManager;
        $this->_movieRepository = $movieRepository;
    }

    #[Route('/movies', name: 'app_movies', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $movies = $this->_movieRepository->findAllWithPagination($page, $limit);

        if (!$movies) {
            return new JsonResponse(
                ['message' => "Aucun film trouvé."],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json($movies, Response::HTTP_OK);
    }

    #[Route('/movie/{id}', name: 'app_movie_show',   methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $movie = $this->_movieRepository->findOneBy(['id' => $id]);

        if (!$movie) {
            return new JsonResponse(
                ['message' => "Aucun film trouvé."],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json($movie, Response::HTTP_OK);
    }

    #[Route('/movie', name: 'app_movie_new', methods: ['POST'], format: 'json')]
    public function createMovie(Request $request, ValidatorInterface $validator): Response
    {
        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData)) {
            return new Response(
                "Une erreur s'est produite lors du traitement de votre demande. Veuillez réessayer ultérieurement.",
                Response::HTTP_BAD_REQUEST
            );
        }

        $movie = new Movie();
        $movie->setName($requestData['name']);
        $movie->setDescription($requestData['description']);
        $movie->setReleaseAt($requestData['releaseAt']);
        $movie->setRating($requestData['rating']);

        $this->_entityManager->persist($movie);

        $errors = $validator->validate($movie);
        if (count($errors) > 0) {
            return new JsonResponse(['message' => 'Erreur de validation', 'errors' => (string) $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->_entityManager->flush();

        return new Response('Film créé avec succès!', Response::HTTP_CREATED);
    }

    #[Route('/movie/edit/{id}', name: 'app_movie_edit', methods: ['PATCH'], format: 'json')]
    public function update(Request $request, int $id, ValidatorInterface $validator): JsonResponse
    {
        $movie = $this->_movieRepository->findOneBy(['id' => $id]);

        if (!$movie) {
            return new JsonResponse(['message' => 'Film non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true);

        if (!$requestData) {
            return new Response(
                "Une erreur s'est produite lors du traitement de votre demande. Veuillez réessayer ultérieurement.",
                Response::HTTP_BAD_REQUEST
            );
        }

        foreach ($requestData as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            if (method_exists($movie, $methodName)) {
                $movie->$methodName($value);
            }
        }

        $errors = $validator->validate($movie);
        if (count($errors) > 0) {
            return new JsonResponse(['message' => 'Erreur de validation', 'errors' => (string) $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->_entityManager->flush();

        return new JsonResponse(['message' => 'Film mis à jour avec succès!'], Response::HTTP_OK);
    }

    #[Route('/movie/{id}', name: 'app_movie_delete', methods: ['DELETE'])]
    public function deleteMovie(int $id): Response
    {
        $movie = $this->_movieRepository->findOneBy(['id' => $id]);

        if (!$movie) {
            return new JsonResponse(
                ['message' => "Aucun film trouvé."],
                Response::HTTP_NOT_FOUND
            );
        }

        $this->_entityManager->remove($movie);
        $this->_entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
