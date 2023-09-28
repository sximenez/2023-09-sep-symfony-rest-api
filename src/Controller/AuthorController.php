<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api')]
class AuthorController extends AbstractController
{
    // #[Route('/author', name: 'app_author')]
    // public function index(): Response
    // {
    //     return $this->render('author/index.html.twig', [
    //         'controller_name' => 'AuthorController',
    //     ]);
    // }

    // --------------- ROUTE -------------------

    #[Route('/authors', name: 'author', methods: ['GET'])]
    public function getAuthorList(AuthorRepository $authorRepository, SerializerInterface $serializer): JsonResponse
    {

        $authorList = $authorRepository->findAll();
        $jsonAuthorList = $serializer->serialize($authorList, 'json', ['groups' => 'getAuthors']);

        return new JsonResponse($jsonAuthorList, Response::HTTP_OK, [], true);
    }

    // --------------- ROUTE -------------------

    #[Route('/authors/{id}', name: 'detailAuthor', methods: ['GET'])]
    public function getOneAuthor(Author $author, SerializerInterface $serializer): JsonResponse
    {
        $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getAuthors']);
        return new JsonResponse($jsonAuthor, Response::HTTP_OK, [], true);
    }

    // --------------- ROUTE -------------------

    #[Route('/authors', name: 'createAuthor', methods: ['POST'])]
    public function createAuthor(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, BookRepository $bookRepository): JsonResponse
    {

        // Deserialize the JSON body into an object
        $author = $serializer->deserialize($request->getContent(), Author::class, 'json');

        // Turn the request into an array, and extract the ids
        $content = $request->toArray();
        $idBooks = $content['idBooks'] ?? [];

        // Loop on each id and add it to the author's books if it exists
        foreach ($idBooks as $id) {
            $book = $bookRepository->find($id);
            if ($book) {
                $author->addBook($book);
            }
        }

        // Persist and flush to the DB
        $em->persist($author);
        $em->flush();

        // Return a JSON response to the console 
        $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getAuthors']);
        $location = $urlGenerator->generate('detailAuthor', ['id' => $author->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonAuthor, Response::HTTP_CREATED, ["location" => $location], true);
    }

    // --------------- ROUTE -------------------

    #[Route('/authors/{id}', name: 'updateAuthor', methods: ['PUT'])]
    public function updateAuthor(BookRepository $bookRepository, Author $currentAuthor, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, AuthorRepository $authorRepository): JsonResponse
    {
        $updatedAuthor = $serializer->deserialize($request->getContent(), Author::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAuthor]);

        // Turn the request into an array, and extract the ids
        $content = $request->toArray();
        $idBooks = $content['idBooks'] ?? [];

        // Clear book list
        $updatedAuthor->getBooks()->clear();

        // Loop on each id and add it to the author's books if it exists
        foreach ($idBooks as $id) {
            $book = $bookRepository->find($id);
            if ($book) {
                $currentAuthor->addBook($book);
            }
        }

        $em->persist($updatedAuthor);
        $em->flush();

        $jsonUpdatedAuthor = $serializer->serialize($updatedAuthor, 'json', ['groups' => 'getAuthors']);
        return new JsonResponse($jsonUpdatedAuthor, Response::HTTP_OK, [], true);
    }

    // --------------- ROUTE -------------------

    #[Route('/authors/{id}', name: 'deleteAuthor', methods: ['DELETE'])]
    public function deleteAuthor(Author $author, EntityManagerInterface $em): JsonResponse
    {

        $em->remove($author);
        $em->flush();
        // dd($author->getBooks());

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
