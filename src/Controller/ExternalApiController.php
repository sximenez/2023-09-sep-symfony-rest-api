<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Faker\Factory;
use OpenApi\Annotations as OA;

#[Route('/api/external')]
class ExternalApiController extends AbstractController
{

    /**
     * Query planets from the Star Wars API.
     *
     * @OA\Response(
     *     response=200,
     *     description="Recover the list of planets.",
     * )
     * 
     * @OA\Tag(name="External")
     * 
     */
    #[Route('/planets', name: 'app_external_api', methods: 'GET')]
    public function getStarWarsPlanets(HttpClientInterface $client): JsonResponse
    {
        $response = $client->request(
            'GET',
            'https://swapi.dev/api/planets'
        );

        return new JsonResponse($response->getContent(), $response->getStatusCode(), [], true);
    }

    #[Route('/planets', name: 'app_external_api', methods: 'POST')]
    public function persistStarWarsPlanets(HttpClientInterface $client, EntityManagerInterface $em): JsonResponse
    {
        $faker = Factory::create();

        $response = $client->request(
            'GET',
            'https://swapi.dev/api/planets'
        );

        $content = json_decode($response->getContent(), true);
        $limited_content = array_slice($content['results'], 0, 10);

        foreach ($limited_content as $e) {

            $book = new Book;
            $name = $e['name'];
            $summary = $e['climate'];

            // dump($name);

            $book->setTitle($name);
            $book->setCoverText($summary);
            $publicationDate = new \DateTime($faker->date());
            $book->setPublicationDate($publicationDate);

            $em->persist($book);
        }

        $em->flush();

        return new JsonResponse('Items created', Response::HTTP_CREATED, [], false);
    }
}
