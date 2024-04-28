<?php

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HalResponseBuilder
{
    private $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function buildHalResponse($entity, array $links, array $context = []): array
    {
        // Normalisation de l'entité en tableau
        $data = $this->normalizer->normalize($entity, null, $context);
        $data['_links'] = $links;
        return $data;
    }

    public function createLinksForCategory($category): array
    {
        return [
            'self' => ['href' => '/category/' . $category->getId()],
            'movies' => ['href' => '/category/' . $category->getId() . '/movies']
        ];
    }

    public function createLinksForMovie($movie): array
    {
        return [
            'self' => ['href' => '/movie/' . $movie->getId()],
            'categories' => ['href' => '/movie/' . $movie->getId() . '/categories']
        ];
    }
}
