# Symfony REST API

This document is based on the OpenClassrooms [REST API course](https://openclassrooms.com/en/courses/7709361-construisez-une-api-rest-avec-symfony).

## Contents

- [Symfony REST API](#symfony-rest-api)
  - [Contents](#contents)
  - [Initialize the project](#initialize-the-project)
  - [File structure](#file-structure)
  - [Start/stop the server](#startstop-the-server)
  - [Create the database](#create-the-database)
  - [Create an entity](#create-an-entity)
  - [Create an entity relation](#create-an-entity-relation)
    - [Create groups](#create-groups)
  - [Migrate an entity to the database](#migrate-an-entity-to-the-database)
  - [Create fixtures](#create-fixtures)
  - [Create a controller](#create-a-controller)
    - [Create a route](#create-a-route)
    - [Routes common practices](#routes-common-practices)
  - [Consider abstractions](#consider-abstractions)

## Initialize the project

```Terminal
symfony new projectName --webapp --docker --cloud
```

This presupposes that all necessary apps to run the project are already installed: 

Git, PHP, Symfony, Composer, XAMPP, Insomnia...

Use the `--webapp` flag to install all useful bundles.

Use the `--docker` flag to prepate the project for containerization.

Use the `--cloud` flag to prepare the project for deployment on platform.sh.

This creates a ~? boilerplate.

While installing bundles individually can save space, it's more efficient to use the flags.

## File structure

```Mermaid
graph TD;
API --> config;
API --> migrations;
API --> src;
API --> .env;
.env --> 1[[Database]];
src --> Controller;
Controller --> Routes;
src --> DataFixtures;
src --> Entity;
Entity --> Repository;
src --> EventSubscriber;
```

## Start/stop the server

```Terminal
symfony server:start -d
```

```Terminal
symfony server:stop
```

## Create the database

Use the `.env` file as a template (do not write sensible data on it as it will be visible on GitHub).

1. Clone it and name it `.env.local` (or `.env.dev`, `env.prod`).

2. Declare the path and name of the database:

```Terminal
DATABASE_URL="mysql://root:@127.0.0.1:3306/databaseName"
```

3. Create the database in mySQL:

```Terminal
symfony console doctrine:database:create
```

4. Check if the connection is successful (should return 1):

```Terminal
symfony console dbal:run-sql "SELECT 1"
```

## Create an entity

```Terminal
symfony console make:entity
```

This creates a file in the `Entity` dir with the data model.

And a file in the `Repository` dir with the database querying code.

## Create an entity relation

### Create groups

```php

```

## Migrate an entity to the database

```Terminal
symfony console make:migration
```

This creates a file in the `migrations` dir with the SQL code to alter the database.

It's good practice to read and verify it.

```Terminal
symfony console doctrine:migrations:migrate
```

If successful, check the database for confirmation (e.g. phpmyadmin).

## Create fixtures

Install Faker PHP for more realistic test data:

```Terminal
composer require fakerphp/faker
```

If no `DataFixtures` dir exists on `src`, run:

```Terminal
composer require orm-fixtures --dev
```

Add custom fixtures to populate your tables (migrated entities):

```php
 class AppFixtures extends Fixture
14     {
15         private $userPasswordHasher;
16     
17         public function __construct(UserPasswordHasherInterface $userPasswordHasher)
18         {
19             $this->userPasswordHasher = $userPasswordHasher;
20         }
21     
22         public function load(ObjectManager $manager): void
23         {
24     
25             $faker = Factory::create();
26     
27             // User
28             $user = new User();
29             $user->setEmail('user@bookapi.com');
30             $user->setRoles(['ROLE_USER']);
31             $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
32             $manager->persist($user);
33     
34             // Admin
35             $admin = new User();
36             $admin->setEmail('admin@bookapi.com');
37             $admin->setRoles(['ROLE_ADMIN']);
38             $admin->setPassword($this->userPasswordHasher->hashPassword($admin, "password"));
39             $manager->persist($admin);
40     
41             // Author
42             $listAuthor = [];
43             for ($i = 0; $i < 5; $i++) {
44                 // Create an author
45                 $author = new Author();
46                 $author->setFirstName($faker->firstName());
47                 $author->setLastName($faker->lastName());
48                 $manager->persist($author);
49                 // Store it in an array for random picking
50                 $listAuthor[] = $author;
51             }
52     
53             // Book
54             for ($i = 0; $i < 10; $i++) {
55                 $book = new Book;
56                 $book->setTitle($faker->sentence(3, true));
57                 $book->setCoverText($faker->paragraph());
58                 $publicationDate = new \DateTime($faker->date());
59                 $book->setPublicationDate($publicationDate);
60                 $book->setAuthor($listAuthor[array_rand($listAuthor)]);
61                 $manager->persist($book);
62             }
63     
64             $manager->flush();
65         }
66     }
```

```Terminal
symfony console doctrine:fixtures:load
```

If successful, check the database for confirmation (e.g. phpmyadmin).

## Create a controller

```Terminal
symfony console make:controller ControllerName
```

This creates a controller boilerplate where you can specify routes and their functions.

### Create a route

```php
#[Route('/books', name: 'book', methods: ['GET'])]
```

### Routes common practices

1. Set a global `/apiName` prefix before the class:

```php
#[Route('/apiName')]
class BookController extends AbstractController
{
```

2. Use plurals for general categories (e.g. `/api/books`).

3. Return a `JsonResponse` instead of any `Response`.

4. Use `return new JsonResponse()` instead of `return $this->json()`.

5. Use `methods: ['GET']` to specify that only GET requests can be processed.

## Consider abstractions

When developing the API, the main goal is to keep the code understandable. 

Use abstraction (code bundling, e.g. services) only when needed, whenever code can really be reused.

Over-abstraction can ultimately lead to time waste and file complexity.
