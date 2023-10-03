<?php

namespace App\Controller;

use App\Entity\Book;
use App\Service\crudService;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('/api')]
class BookController extends AbstractController
{
    // #[Route('/book', name: 'app_book')]
    // public function index(): Response
    // {
    //     return $this->render('book/index.html.twig', [
    //         'controller_name' => 'BookController',
    //     ]);
    // }

    private $bookRepository;
    private $serializer;
    // private $crudService;
    private $em;

    public function __construct(BookRepository $bookRepository, SerializerInterface $serializer, crudService $crudService, EntityManagerInterface $em)
    {
        $this->bookRepository = $bookRepository;
        $this->serializer = $serializer;
        $this->em = $em;
        // $this->crudService = $crudService;
    }

    /**
     * This is a test.
     *
     * @OA\Response(
     *     response=200,
     *     description="Recover the list of books.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Book::class, groups={"getBooks"}))
     *     )
     * )
     * 
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Number of results",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Books")
     * 
     */
    #[Route('/books', name: 'book', methods: ['GET'])]
    // public function getAll(): JsonResponse
    // {
    //     $jsonData = $this->crudService->getAll($this->bookRepository, $this->serializer, ['getBooks']);
    //     return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    // }

    public function getBooks(Request $request, TagAwareCacheInterface $cache, BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = 'getBooks-' . $page . "-" . $limit;

        // $bookList = $this->bookRepository->findAll();
        // $bookList = $this->bookRepository->findAllWithPagination($page, $limit);

        $jsonBookList = $cache->get($idCache, function (ItemInterface $item) use ($bookRepository, $page, $limit, $serializer) {
            // echo ('Cache has been set!');
            $item->tag('booksCache');
            $item->expiresAfter(60);
            $bookList = $bookRepository->findAllWithPagination($page, $limit);
            return $serializer->serialize($bookList, 'json', ['groups' => 'getBooks']);
        });

        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }

    #[Route('/books/{id}', name: 'detailBook', methods: ['GET'])]
    // public function getOne(Book $book): JsonResponse
    // {
    //     $jsonData = $this->crudService->getOne($book, $this->serializer, ['getBooks']);
    //     return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    // }

    public function getBook(Book $book): JsonResponse
    {
        $jsonBook = $this->serializer->serialize($book, 'json', ['groups' => 'getBooks']);
        return new JsonResponse($jsonBook, Response::HTTP_OK, [], true);
    }

    #[Route('/books/{id}', name: 'deleteBook', methods: ['DELETE'])]
    public function deleteBook(Book $book, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse
    {
        $cache->invalidateTags(['booksCache']);
        $em->remove($book);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/books', name: 'createBook', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'You don\'t have access.')]
    public function createBook(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, AuthorRepository $authorRepository, ValidatorInterface $validator): JsonResponse
    {

        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');

        // Error verification
        $errors = $validator->validate($book);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $idAuthor = $content['idAuthor'] ?? -1;

        $book->setAuthor($authorRepository->find($idAuthor));

        $em->persist($book);
        $em->flush();

        $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'getBooks']);
        $location = $urlGenerator->generate('detailBook', ['id' => $book->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["location" => $location], true);
    }

    #[Route('/books/{id}', name: 'updateBook', methods: ['PUT'])]
    public function updateBook(Book $currentBook, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, AuthorRepository $authorRepository): JsonResponse
    {
        $updatedBook = $serializer->deserialize($request->getContent(), Book::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentBook]);

        $content = $request->toArray();
        $idAuthor = $content['idAuthor'] ?? -1;

        $updatedBook->setAuthor($authorRepository->find($idAuthor));

        $em->persist($updatedBook);
        $em->flush();

        $jsonUpdatedBook = $serializer->serialize($updatedBook, 'json', ['groups' => 'getBooks']);
        return new JsonResponse($jsonUpdatedBook, Response::HTTP_OK, [], true);
    }
}
