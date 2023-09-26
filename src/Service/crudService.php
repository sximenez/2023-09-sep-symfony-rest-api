<?php

namespace App\Service;

use App\Entity\Book;
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

class crudService
{

  private $crudService;

  public function __construct(EntityManagerInterface $crudService)
  {
    $this->crudService = $crudService;
  }

  public function getAll($repository, $serializer, $groups = [])
  {
    $data = $repository->findAll();
    $jsonData = $serializer->serialize($data, 'json', ['groups' => $groups]);

    return $jsonData;
  }

  public function getOne($entity, $serializer, $groups = [])
  {
    $jsonData = $serializer->serialize($entity, 'json', ['groups' => $groups]);
    return $jsonData;
  }
}
