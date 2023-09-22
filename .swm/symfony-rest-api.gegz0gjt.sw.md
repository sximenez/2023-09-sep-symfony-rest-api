---
id: gegz0gjt
title: Symfony REST API
file_version: 1.1.3
app_version: 1.17.0
---

## Objective

Develop a complete REST API using PHP and Symfony.

## REST API Architecture

An API is an application: Application Programming Interface.

The aim of an API: to manage resources (text, images, files, etc.).

This management is done via HTTP actions: GET, POST, DELETE, PUT.

REST is an architecture: Representational State Transfer.

It is a workframe for consistent API development.

APIs are developed by developers for developers.

In short, they are a set of actions configured to manage given resources.

### HTTP

HTTP is a protocol, a communication method between two computers.

An API is an app that receives an HTTP request and serves a HTTP response.

### HTTP request

An HTTP request is made by a client and contains:

2.  The HTTP method (GET, POST, etc.)

3.  A URI (what comes after the domain name)

4.  The protocol version

5.  The headers

6.  The body (the content of the request)

HTTP post request example:

```
POST /users HTTP/1.1 User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2)
AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36
Content-Type: application/x-www-form-urlencoded Content-Length: 28 name=Sarah
Khalil&job=auteur
```

### HTTP methods:

1.  GET: retrieve information related to the URI

    1.  Safe method since it doesn't write modify data on the server

    2.  Idempotent: the API must always send the same response for a same request

2.  POST: create a resource on the server with needed data within the body

3.  PUT: replace information of a resource on the server with needed data within the body

4.  PATCH: alter information of a resource on the server with desired action within the body

5.  DELETE: remove a resource from the server for a given URI

6.  OPTIONS: obtain information on possible actions that can be made on a resource

7.  CONNECT: establish a first connection with the server for a given URI

8.  HEAD: same as GET, but only headers are served

9.  TRACE: trace the server path taken by the request

### HTTP response

An HTTP response is served by the server and contains:

2.  The protocol version

3.  The status code (200, 404, etc.)

4.  The status code in text (OK, Not found, etc.)

5.  The headers

6.  The body (the content of the response)

HTTP response example:

```
HTTP/1.1 200 OK Date:Tue, 31 Jan 2017 13:18:38 GMT Content-Type: application/json {
 "current status" : "Everything is ok!" }
```

### Code status

1.  1xx: information: used to inform the client

2.  2xx: success: everything went well

3.  3xx: redirections

4.  4xx: error coming from the request (client)

5.  5xx: error coming from the server

## From REST to RESTful

### Richardson Maturity Model (RMM)

Since REST is an architecture (convention), the more an API is RESTful, the more organized and understandable it will be for developers.

This model helps measure that RESTfulness.

There are four levels.

### Level 0: Swamp of POX (plain old XML):

In applications communication, there is the concept of encapsulation:

- Applications within a system can integrate (share data) by calling each other directly over a network.

- Encapsulation reduces the need for duplicate data within each application.

- However, a system with many calls can become a knot and create issues.

  - _People often design the integration the way they would design a single application, unaware that the rules change. -_ Gregor Hohpe

- In practice, a level 0 API can usually be queried on a single point of entry for all actions.

- In this example `/appointmentService`

```

-> Request
POST /appointmentService HTTP/1.1
[various other headers]

<openSlotRequest date = "2010-01-04" doctor = "mjones"/>

<- Reply
HTTP/1.1 200 OK
[various headers]

<openSlotList>
  <slot start = "1400" end = "1450">
    <doctor id = "mjones"/>
  </slot>
  <slot start = "1600" end = "1650">
    <doctor id = "mjones"/>
  </slot>
</openSlotList>

-> Request
POST /appointmentService HTTP/1.1
[various other headers]

<appointmentRequest>
  <slot doctor = "mjones" start = "1400" end = "1450"/>
  <patient id = "jsmith"/>
</appointmentRequest>

<- Reply
HTTP/1.1 200 OK
[various headers]

<appointment>
  <slot doctor = "mjones" start = "1400" end = "1450"/>
  <patient id = "jsmith"/>
</appointment>
```

### Level 1: Resources:

Instead of having one point of entry, a level 1 API has many, using ids.

- In this example `/doctors/mjones`, the doctor and the slots are targeted individually via the URI:

```
-> Request
POST /doctors/mjones HTTP/1.1
[various other headers]

<openSlotRequest date = "2010-01-04"/>

<- Reply
HTTP/1.1 200 OK
[various headers]


<openSlotList>
  <slot id = "1234" doctor = "mjones" start = "1400" end = "1450"/>
  <slot id = "5678" doctor = "mjones" start = "1600" end = "1650"/>
</openSlotList>

-> Request
POST /slots/1234 HTTP/1.1
[various other headers]

<appointmentRequest>
  <patient id = "jsmith"/>
</appointmentRequest>

<- Reply
HTTP/1.1 200 OK
[various headers]

<appointment>
  <slot id = "1234" doctor = "mjones" start = "1400" end = "1450"/>
  <patient id = "jsmith"/>
</appointment>
```

- The main difference is we can now call a method on one particular resource by providing arguments in the URI:

  - To update an appointment for example `http://hospital.com/slots/1234/update-method`

### Level 2: HTTP methods

A level 2 API leverages HTTP verbs for requests and HTTP error codes for replies:

```
-> Request
GET /doctors/mjones/slots?date=20100104&status=open HTTP/1.1
Host: royalhope.nhs.uk

<- Reply
HTTP/1.1 200 OK
[various headers]

<openSlotList>
  <slot id = "1234" doctor = "mjones" start = "1400" end = "1450"/>
  <slot id = "5678" doctor = "mjones" start = "1600" end = "1650"/>
</openSlotList>

-> Request
POST /slots/1234 HTTP/1.1
[various other headers]

<appointmentRequest>
  <patient id = "jsmith"/>
</appointmentRequest>

<- Reply
HTTP/1.1 201 Created
Location: slots/1234/appointment
[various headers]

<appointment>
  <slot id = "1234" doctor = "mjones" start = "1400" end = "1450"/>
  <patient id = "jsmith"/>
</appointment>
```

- By using HTTP verbs, we can omit the action in level 1 `http://hospital.com/slots/1234/update-method`

  - We can now use GET to receive information, POST to change state, PUT to update information, etc. all using the same URI.

- By using GET (a safe method not changing state), we can leverage caching, a key concept in web.

- It's important to leverage safe (GET) vs non-safe actions, plus status codes in replies.

  - The clearer status codes are, the higher the quality of the API.

### Level 3: Hypermedia controls

- This level introduces HATEOAS (Hypertext as the Engine of Application State):

```
-> Request
GET /doctors/mjones/slots?date=20100104&status=open HTTP/1.1
Host: royalhope.nhs.uk

<- Reply
HTTP/1.1 200 OK
[various headers]

<openSlotList>
  <slot id = "1234" doctor = "mjones" start = "1400" end = "1450">
     <link rel = "/linkrels/slot/book"
           uri = "/slots/1234"/>
  </slot>
  <slot id = "5678" doctor = "mjones" start = "1600" end = "1650">
     <link rel = "/linkrels/slot/book"
           uri = "/slots/5678"/>
  </slot>
</openSlotList>

-> Request
POST /slots/1234 HTTP/1.1
[various other headers]

<appointmentRequest>
  <patient id = "jsmith"/>
</appointmentRequest>

<- Reply
HTTP/1.1 201 Created
Location: http://royalhope.nhs.uk/slots/1234/appointment
[various headers]

<appointment>
  <slot id = "1234" doctor = "mjones" start = "1400" end = "1450"/>
  <patient id = "jsmith"/>
  <link rel = "/linkrels/appointment/cancel"
        uri = "/slots/1234/appointment"/>
  <link rel = "/linkrels/appointment/addTest"
        uri = "/slots/1234/appointment/tests"/>
  <link rel = "self"
        uri = "/slots/1234/appointment"/>
  <link rel = "/linkrels/appointment/changeTime"
        uri = "/doctors/mjones/slots?date=20100104&status=open"/>
  <link rel = "/linkrels/appointment/updateContactInfo"
        uri = "/patients/jsmith/contactInfo"/>
  <link rel = "/linkrels/help"
        uri = "/help/appointment"/>
</appointment>
```

- Here, the response provides links with URIs:

  - It tells us what can be done (rel) and how (uri).

Remember, REST is a model to improve interactions between HTTP services:

```
[The attractiveness of this model is] its relationship to common design techniques:

- Level 1 tackles the question of handling complexity by using **divide and conquer**, breaking a large service endpoint down into multiple resources.

- Level 2 introduces a standard set of verbs so that we handle similar situations in the same way, **removing unnecessary variation**.

- Level 3 introduces **discoverability**, providing a way of making a protocol more self-documenting.

The result is a model that **helps us think about the kind of HTTP service we want to provide** and frame the expectations of people looking to interact with it.
```

Source: [https://martinfowler.com/articles/richardsonMaturityModel.html](https://martinfowler.com/articles/richardsonMaturityModel.html)

### REST API limitations

1.  Client / Server:

    1.  The client makes an HTTP request (browser, React app)

    2.  The server serves a response to the request (Symfony app)

2.  Stateless:

    1.  There is no relationship between requests

    2.  There is no session on the server, only on the client

3.  Cache HTTP: avoid generating a same response twice (resource management)

4.  Layout: a client must receive a response, regardless of what happens on the server

5.  Uniform interface: a resource must have:

    1.  An unique identifier (ID, UID)

    2.  Data (e.g. JSON)

    3.  Autodescription: header (XML, JSON, HTML)

6.  Code on demand (optional): a server can send scripts to the client, who executes them or not

## Initializing the project

```
symfony new Books
cd Books
symfony server:start -d
```

This command generates a 10MB boilerplate, which is light.

However, it doesn't include Symfony Profiler for debugging.

The `composer require webapp`command installs it with other bundles, but increases the size of the project to 100MB.

I tested installing the Profiler bundle via the command:

```
composer require symfony/web-profiler-bundle --dev
```

The file size stayed light at 15MB, and Twig was installed as well.

## Creating entities

An entity is a data model.

For the moment, we have three bundles installed (sort of plug-ins): Symfony, Twig and Profiler.

```
<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
];
```

To create an entity, we need to install the maker bundle to benefit from Symfony's automation:

```
 composer require symfony/maker-bundle --dev
```

The file size is now at 20MB.

We need to install a last bundle, Doctrine.

Doctrine is an ORM (Object Relational Mapper).

It translates tables (relational databases) into objects (JSON) and viceversa.

```
composer require orm
```

Installing Doctrine increases the file size to 30MB.

The bundle set up now looks like this:

```
<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
];
```

To create an entity:

```
symfony console make:entity
```

This command creates an entity and a repository, the repository being a sort of manager of the entity:

<br/>

<!--MERMAID {width:100}-->

```mermaid
graph LR
Repository \-\-\> Entity
```

<!--MCONTENT {content: "graph LR<br/>\nRepository \\-\\-\\> Entity<br/>"} --->

<br/>

## Creating the database

To connect Symfony to a database, we use the `.env` file.

Sensitive data like database logins should never be pushed to GitHub.

This is why it is good practice to use the `.env` file like a template.

You can copy it to create `.env.local`, `.env.dev` and `.env.prod`, according to Symfony's environments.

Copy `.env` into `.env.local` and declare the database login.

<br/>

This file was generated by Swimm. [Click here to view it in the app](https://app.swimm.io/repos/Z2l0aHViJTNBJTNBMjAyMy0wOS1zZXB0LXN5bWZvbnktcmVzdC1hcGklM0ElM0FzeGltZW5leg==/docs/gegz0gjt).
