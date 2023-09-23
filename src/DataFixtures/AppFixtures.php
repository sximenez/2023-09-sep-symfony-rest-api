<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Author;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        // Author init
        $listAuthor = [];
        for ($i = 0; $i < 10; $i++) {
            // Création de l'auteur lui-même.
            $author = new Author();
            $author->setFirstName("Name " . $i);
            $author->setLastName("Surname " . $i);
            $manager->persist($author);
            // On sauvegarde l'auteur créé dans un tableau.
            $listAuthor[] = $author;
        }

        // Book init
        for ($i = 0; $i < 20; $i++) {
            $book = new Book;
            $book->setTitle('Book ' . $i);
            $book->setCoverText('Hello world : ' . $i);
            $book->setAuthor($listAuthor[array_rand($listAuthor)]);
            $manager->persist($book);
        }

        $manager->flush();
    }
}
