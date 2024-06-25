<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Service\HalResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MovieController extends AbstractController
{
	private $_entityManager;
	private $_movieRepository;
	private $logger;

	public function __construct(
		EntityManagerInterface $entityManager,
		MovieRepository $movieRepository,
		LoggerInterface $logger
	) {
		$this->_entityManager = $entityManager;
		$this->_movieRepository = $movieRepository;
		$this->logger = $logger;
	}

	#[Route('/movies', methods: ['GET'])]
	public function list(Request $request, HalResponseBuilder $halResponse): JsonResponse
	{
		try {
			$page = $request->query->getInt('page', 1);
			$limit = $request->query->getInt('limit', 3);

			if ($page < 1 || $limit < 1) {
				throw new HttpException(Response::HTTP_BAD_REQUEST, 'Les paramètres de recherche sont invalides.');
			}

			$movies = $this->_movieRepository->findAllWithPagination($page, $limit);

			if (!$movies) {
				return new JsonResponse([], Response::HTTP_NO_CONTENT);
			}

			$halMovies = [];
			foreach ($movies as $movie) {
				$links = $halResponse->createLinksForMovie($movie);
				$movieData = $halResponse->buildHalResponse($movie, $links, ['groups' => 'movie.index']);
				$halMovies[] = $movieData;
			}

			return $this->json($halMovies, Response::HTTP_OK, [], ['groups' => 'movie.index']);
		} catch (HttpException $e) {
			return $this->json(['message' => $e->getMessage()], $e->getStatusCode());
		} catch (\Exception $e) {

			return $this->json(['message' => 'Erreur interne du serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	#[Route('/movies/{id}', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
	public function show(int $id, HalResponseBuilder $halResponse): JsonResponse
	{
		try {
			$movie = $this->_entityManager->getRepository(Movie::class)->find($id);

			if (!$movie) {
				throw new NotFoundHttpException('Le film est inconnu.');
			}
			$links = $halResponse->createLinksForMovie($movie);
			$movieData = $halResponse->buildHalResponse($movie, $links, ['groups' => 'movie.index']);

			return $this->json($movieData, Response::HTTP_OK);
		} catch (NotFoundHttpException $e) {
			return $this->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
		} catch (\Exception $e) {
			return $this->json(['message' => 'Erreur interne du serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	#[Route('/movies', name: 'app_movie_new', methods: ['POST'], format: 'json')]
	public function createMovie(Request $request, ValidatorInterface $validator): Response
	{
		try {
			$requestData = json_decode($request->getContent(), true);

			if (!$requestData) {
				return new JsonResponse(
					['message' => "Les paramètres de recherche sont invalides."],
					Response::HTTP_BAD_REQUEST
				);
			}

			$movie = new Movie();
			$movie->setName($requestData['name'] ?? '');
			$movie->setDescription($requestData['description'] ?? '');
			$movie->setDuration($requestData['duration'] ?? 0);
			$movie->setRating($requestData['rating'] ?? 0);

			$errors = $validator->validate($movie);
			if (count($errors) > 0) {
				return new JsonResponse(['message' => 'Erreur de validation', 'errors' => (string) $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
			}

			$this->_entityManager->persist($movie);
			$this->_entityManager->flush();

			return new JsonResponse(['message' => 'Film créé avec succès!'], Response::HTTP_CREATED);
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());

			return new JsonResponse(['message' => 'Erreur interne du serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	#[Route('/movies/{id}', name: 'app_movie_edit', methods: ['PUT'], format: 'json')]
	public function update(Request $request, int $id, ValidatorInterface $validator): JsonResponse
	{
		try {
			$movie = $this->_movieRepository->find($id);

			if (!$movie) {
				return new JsonResponse(['message' => 'Film non trouvé'], Response::HTTP_NOT_FOUND);
			}

			$requestData = json_decode($request->getContent(), true);

			if (!$requestData) {
				return new JsonResponse(
					['message' => "Les paramètres de recherche sont invalides."],
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
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());

			return new JsonResponse(['message' => 'Erreur interne du serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}


	#[Route('/movies/{id}', name: 'app_movie_delete', methods: ['DELETE'])]
	public function deleteMovie(int $id): Response
	{
		try {
			$movie = $this->_movieRepository->find($id);

			if (!$movie) {
				return new JsonResponse(
					['message' => "Aucun film trouvé."],
					Response::HTTP_NOT_FOUND
				);
			}

			$this->_entityManager->remove($movie);
			$this->_entityManager->flush();

			return new Response(null, Response::HTTP_NO_CONTENT);
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());

			return new JsonResponse(['message' => 'Erreur interne du serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}
}
