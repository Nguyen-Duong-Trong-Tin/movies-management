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
---

## 🎨 Frontend Assets (Webpack Encore)

Managing CSS, JavaScript, and images in Symfony is handled beautifully by Webpack Encore, which bridges your PHP backend with modern frontend Node.js tools.

### 1. Installation & Setup
First, install the Symfony bundle, which will generate your `assets/` folder and `webpack.config.js` file. Then, install the Node dependencies.
```bash
composer require symfony/webpack-encore-bundle
npm install
```

### 2. Compiling Your Assets
Whenever you write new CSS or JavaScript inside the `assets/` folder, you must compile it into browser-ready files (which get saved to `public/build/`).

*   **Compile Once (Development):** Use this to build your files manually.
    ```bash
    npm run dev
    ```
*   **Auto-Compile (Watch Mode):** Use this while actively coding. It runs in the background and automatically recompiles every time you hit save.
    *(Optional: Install notifier for desktop pop-ups: `npm i webpack-notifier --save-dev`)*
    ```bash
    npm run watch
    ```

### 3. Managing Static Assets (Images & Fonts)
To reliably link to static files in your `public/` directory without hardcoding fragile URLs, install the Asset component:
```bash
composer require symfony/asset
```

**Example Usage in Twig:**
```twig
{# ❌ Bad: Hardcoded URL #}
<img src="/images/logo.png" alt="Movie Logo">

{# ✅ Good: Dynamic Asset URL #}
<img src="{{ asset('images/logo.png') }}" alt="Movie Logo">
```

### 4. Linking CSS in Twig
When you want to load your compiled CSS into your templates, you can use the built-in Encore function, or you can manually link to the built file using the `asset()` function:

```twig
{# templates/base.html.twig #}
{% block stylesheets %}
    {# {{ encore_entry_link_tags('app') }} #}
    <link rel="stylesheet" href="{{ asset('build/app.css') }}">
{% endblock %}
```

### 5. JavaScript Architecture
There are two main ways to structure your JavaScript, depending on where you want the code to run.

#### Option 1: Global JavaScript (Runs on EVERY page)
Use this for scripts that dictate the overall layout, like navigation menus or global site themes.

**1. Create the script:**
```javascript
// assets/javascript/method1.js
console.log('12345');
```

**2. Import it into your main app file:**
```javascript
// assets/app.js
// Compile new javascript file
import './javascript/method1.js';
```

**3. Render it in your base template:**
```twig
{# templates/base.html.twig #}
{% block javascripts %}
    {{ encore_entry_script_tags('app') }}
{% endblock %}
```

#### Option 2: Page-Specific JavaScript (Runs ONLY on specific pages)
Use this for heavy scripts that you don't want slowing down your whole site.

**1. Create the script:**
```javascript
// assets/javascript/method2.js
alert('12345');
```

**2. Register a new entry point in Webpack:**
```javascript
// webpack.config.js
Encore
    // ...
    .addEntry('app', './assets/app.js')
    .addEntry('method2', './assets/javascript/method2.js') 
```

**3. Render it ONLY on the specific Twig template:**
```twig
{# templates/movies/show.html.twig #}
{% extends 'base.html.twig' %}

{% block javascripts %}
    {{ encore_entry_script_tags('method2') }}
{% endblock %}
```

---
