<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Book;

class BookController extends AbstractController
{
    // #[Route('/book', name: 'app_book')]
    // public function index(): Response
    // {
    //     return $this->render('book/index.html.twig', [
    //         'controller_name' => 'BookController',
    //     ]);
    // }

    #[Route('api/books', name: 'book', methods: ['GET'])]
    public function getBookList(BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {

        $bookList = $bookRepository->findAll();
        $jsonBookList = $serializer->serialize($bookList, 'json');

        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }

    #[Route('api/books/{id}', name: 'detailBook', methods: ['GET'])]
    public function getOneBook(Book $book, SerializerInterface $serializer): JsonResponse
    {
        $jsonBook = $serializer->serialize($book, 'json');
        return new JsonResponse($jsonBook, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
