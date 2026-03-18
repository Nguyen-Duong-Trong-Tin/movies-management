# 🎬 Movies Management (Symfony 6.4)

A Symfony project for managing movies and actors, demonstrating database relationships, data fixtures, and basic repository queries.

## 🚀 Getting Started

**Create a new project**
```bash
symfony new movies-management --version="6.4.*"
```

**Running the development server**
Start the server in the background (daemon) on a specific port:
```bash
symfony server:start -d --port=8081
```

Stop the server:
```bash
symfony server:stop
```

---

## 📦 Dependencies & Setup

**Annotations**
> **Note:** If you are using PHP 8.0 or higher, this library is generally not necessary as Symfony now uses native PHP Attributes.
```bash
composer require doctrine/annotations
```

**Code Generation Tools (Maker Bundle)**
```bash
composer require --dev maker
symfony console make:controller MoviesController
```

**View Engine (Twig)**
```bash
composer require twig
```

---

## 🗄️ Database Management

**Install Doctrine ORM**
```bash
composer require doctrine
```

**Workflow for creating and updating tables:**
```bash
# 1. Create or update an entity class
symfony console make:entity Movie

# 2. Generate the SQL instructions based on your entities
symfony console make:migration

# 3. Execute the SQL against your database
symfony console doctrine:migrations:migrate
```

---

## 🎭 Data Fixtures (Faking Data)

**Install the Fixtures Bundle**
```bash
composer require --dev doctrine/doctrine-fixtures-bundle
```

**Load fixtures into the database**
```bash
symfony console doctrine:fixtures:load
```

### Example: Seeding a ManyToMany Relationship
This example demonstrates how to create independent entities (`Actor`), save them to memory using `$this->addReference()`, and then assign them to a dependent entity (`Movie`) to populate the pivot table.

```php
<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ActorFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $actor1 = new Actor();
        $actor1->setName('Leonardo DiCaprio');
        $manager->persist($actor1);
        $this->addReference('actor_1', $actor1);

        $actor2 = new Actor();
        $actor2->setName('Joseph Gordon-Levitt');
        $manager->persist($actor2);
        $this->addReference('actor_2', $actor2);

        $actor3 = new Actor();
        $actor3->setName('Ellen Page');
        $manager->persist($actor3);
        $this->addReference('actor_3', $actor3);

        $actor4 = new Actor();
        $actor4->setName('Christian Bale');
        $manager->persist($actor4);
        $this->addReference('actor_4', $actor4);

        $manager->flush();
    }
}

class MovieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $movie1 = new Movie();
        $movie1->setTitle('Inception');
        $movie1->setReleaseYear(2010);
        $movie1->setDescription('A thief who steals corporate secrets.');
        $movie1->setImagePath('[https://res.cloudinary.com/df7e20fdm/image/upload/v1736868496/v3wkykrgehd8ut3izabt.jpg](https://res.cloudinary.com/df7e20fdm/image/upload/v1736868496/v3wkykrgehd8ut3izabt.jpg)');
        $movie1->addActor($this->getReference('actor_1', Actor::class));
        $movie1->addActor($this->getReference('actor_2', Actor::class));
        $manager->persist($movie1);

        $movie2 = new Movie();
        $movie2->setTitle('The Dark Knight');
        $movie2->setReleaseYear(2008);
        $movie2->setDescription('When the menace known as the Joker emerges.');
        $movie2->setImagePath('[https://res.cloudinary.com/df7e20fdm/image/upload/v1740715001/opg7my9qpl7cn98vo121.jpg](https://res.cloudinary.com/df7e20fdm/image/upload/v1740715001/opg7my9qpl7cn98vo121.jpg)');
        $movie2->addActor($this->getReference('actor_3', Actor::class));
        $movie2->addActor($this->getReference('actor_4', Actor::class));
        $manager->persist($movie2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActorFixtures::class,
        ];
    }
}
```

---

## 🎮 Controller & Repository Usage

Here is a quick reference for querying the database using Doctrine's built-in repository methods within a controller.

| Doctrine Method | SQL Equivalent |
| :--- | :--- |
| `$repository->findAll()` | `SELECT * FROM movies;` |
| `$repository->find(1)` | `SELECT * FROM movies WHERE id = 1;` |
| `$repository->findBy([], ['id' => 'DESC'])` | `SELECT * FROM movies ORDER BY id DESC;` |
| `$repository->findOneBy(['id' => 1, 'title' => 'The Dark Knight'])` | `SELECT * FROM movies WHERE id = 1 AND title = 'The Dark Knight' LIMIT 1;` |
| `$repository->count([])` | `SELECT COUNT(id) FROM movies;` |

**Example Implementation:**

```php
<?php

namespace App\Controller;

use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

final class MoviesController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/movies', name: 'app_movies')]
    public function index(): Response
    {
        $repository = $this->entityManager->getRepository(Movie::class);
        $movies = $repository->findAll();

        // dd($movies); // Dump and die for debugging

        return $this->render('index.html.twig', [
            'movies' => $movies,
        ]);
    }
}
```
