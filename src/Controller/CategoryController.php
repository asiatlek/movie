<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Movie;
use Symfony\Component\Routing\Requirement\Requirement;

class CategoryController extends AbstractController
{
    private $_entityManager;
    private $_categoryRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository
    ) {
        $this->_entityManager = $entityManager;
        $this->_categoryRepository = $categoryRepository;
    }


    #[Route('/categories', methods: ['GET'])]
    public function getCategories(): JsonResponse
    {
        $categorys = $this->_categoryRepository->findAll();
        if (!$categorys) {
            return new JsonResponse(['error' => 'Erreur sur la requete'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($categorys, Response::HTTP_OK, [], ['groups' => 'category.index']);
    }


    #[Route('/category/{id}', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function getCategoryById(Category $category): JsonResponse
    {
        return $this->json($category, Response::HTTP_OK, [], ['groups' => 'category.index']);
    }

    #[Route('/category', methods: ['POST'])]
    public function createCategory(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData)) {
            return new JsonResponse(['error' => 'Erreur sur la requete'], Response::HTTP_BAD_REQUEST);
        }

        $category = new Category();
        $category->setName($requestData['name']);

        $this->_entityManager->persist($category);
        $this->_entityManager->flush();

        return new Response('Catégorie créé avec succès!', Response::HTTP_CREATED);
    }

    #[Route('/category/{id}', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
    public function updateCategory(Category $category, Request $request): JsonResponse
    {
        if (!$category) {
            return new JsonResponse(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData) || !isset($requestData['name'])) {
            return new JsonResponse('Erreur sur la requete', Response::HTTP_BAD_REQUEST);
        }

        $category->setName($requestData['name']);

        $this->_entityManager->flush();

        return new JsonResponse(['success' => 'Category updated successfully'], Response::HTTP_OK);
    }

    #[Route('/category/{id}', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function deleteCategory(int $id): Response
    {
        $category = $this->_categoryRepository->find($id);

        if (!$category) {
            return new JsonResponse(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $this->_entityManager->remove($category);
        $this->_entityManager->flush();

        return new JsonResponse(['success' => 'Category deleted successfully'], Response::HTTP_OK);
    }

    #[Route('/category/{id}/add-movie/{movieId}', methods: ['POST'], requirements: ['id' => Requirement::DIGITS, 'movieId' => Requirement::DIGITS])]
    public function addMovie(Category $category, Movie $movieId): Response
    {
        if (!$category || !$movieId) {
            return new JsonResponse(['error' => 'Category or Movie not found'], Response::HTTP_NOT_FOUND);
        }

        $category->addMovie($movieId);
        $this->_entityManager->flush();

        return  new JsonResponse(['message' => 'Movie added to category successfully'], Response::HTTP_OK);
    }

    #[Route('/category/{id}/remove-movie/{movieId}', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS, 'movieId' => Requirement::DIGITS])]
    public function removeMovie(Category $category, Movie $movieId): Response
    {
        if (!$category || !$movieId) {
            return $this->json(['message' => 'Category or Movie not found'], Response::HTTP_NOT_FOUND);
        }

        $category->removeMovie($movieId);
        $movieId->removeCategory($category);
        $this->_entityManager->flush();

        return $this->json(['message' => 'Movie removed from category successfully']);
    }

    #[Route('/movie/{id}/categories', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function getCategoriesByMovie(Movie $movie): Response
    {
        $categories = $movie->getCategories();
        return $this->json($categories, 200, [], ['groups' => 'category.info']);
    }

    #[Route('/category/{id}/movies', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function getMoviesByCategory(Category $category): Response
    {
        $movies = $category->getMovies();
        return $this->json($movies, 200, [], ['groups' => 'movie.info']);
    }
}
