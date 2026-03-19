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

## 🌬️ Tailwind CSS v4 Setup

Tailwind v4 is highly optimized and integrates directly through Webpack Encore using PostCSS.

**1. Install the required dependencies:**
```bash
npm install -D tailwindcss @tailwindcss/postcss postcss-loader
```

**2. Create the PostCSS configuration:**
Create a file named `postcss.config.js` at the root of your project:
```javascript
// postcss.config.js
module.exports = {
  plugins: {
    '@tailwindcss/postcss': {},
  }
}
```

**3. Enable PostCSS in Webpack:**
Open `webpack.config.js` and tell Encore to process PostCSS:
```javascript
// webpack.config.js
Encore
    // ...
    .addEntry('app', './assets/app.js')
    .enablePostCssLoader() // <-- Add this line
    .splitEntryChunks()
    // ...
```

**4. Import Tailwind in your main CSS file:**
Open your main stylesheet (e.g., `assets/styles/app.css`) and add this to the very top:
```css
/* assets/styles/app.css */
@import "tailwindcss";
```
*(Ensure this CSS file is imported in your `assets/app.js` file with `import './styles/app.css';`)*

**5. Compile your assets:**
Rebuild your assets so Webpack can process the new Tailwind utilities.
```bash
npm run dev
```

**6. Use Tailwind in your Twig templates:**
Make sure your compiled CSS is linked, then start using Tailwind classes!
```twig
{# templates/base.html.twig #}
{% block stylesheets %}
    {{ encore_entry_link_tags('app') }}
{% endblock %}

<body class="bg-slate-100 text-slate-800 p-8">
    <h1 class="text-2xl font-bold text-blue-600">Tailwind is working! 🎉</h1>
</body>
```

## 🛠️ CRUD Operations

This project implements standard Create, Read, Update, and Delete (CRUD) operations using Symfony Controllers, Doctrine ORM, and Symfony Forms.

### 1. Setup Form Dependencies
Before building the create and update features, ensure the form and mime components are installed:
```bash
composer require symfony/form
composer require symfony/mime
symfony console make:form MovieFormType Movie
```

---

### 📖 READ

**Controller (`src/Controller/MoviesController.php`):**
```php
#[Route('/movies', name: 'movies_index', methods: ['GET'])]
public function index()
{
    $repository = $this->entityManager->getRepository(Movie::class);
    
    return $this->render('movies/index.html.twig', [
        'movies' => $repository->findAll(),
    ]);
}

#[Route('/movies/{id}', name: 'movies_show', methods: ['GET'])]
public function show(Movie $movie)
{
    return $this->render('movies/show.html.twig', [
        'movie' => $movie,
    ]);
}
```

**Template (`templates/movies/index.html.twig`):**
```twig
{% for movie in movies %}
    <div>
        <img src="{{ movie.imagePath }}" />
        <h2>{{ movie.title }}</h2>
        <p class="text-base text-gray-700 pt-4 pb-10 leading-8 font-light">
            {{ movie.description }}
        </p>
        <a href="/movies/{{ movie.id }}">Keep Reading</a>
    </div>
{% endfor %}
```

---

### ➕ CREATE

**Form Type (`src/Form/MovieFormType.php`):**
```php
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('title', TextType::class, [
            'attr' => [
                'class' => 'bg-transparent block border-b-2 w-full h-20 text-6xl outline-none',
                'placeholder' => 'Enter title...'
            ],
            'label' => false
        ])
        ->add('releaseYear', IntegerType::class, [
            'attr' => [
                'class' => 'bg-transparent block mt-10 border-b-2 w-full h-20 text-6xl outline-none',
                'placeholder' => 'Enter Release Year...'
            ],
            'label' => false
        ])
        ->add('description', TextareaType::class, [
            'attr' => [
                'class' => 'bg-transparent block mt-10 border-b-2 w-full h-60 text-6xl outline-none',
                'placeholder' => 'Enter Description...'
            ],
            'label' => false
        ])
        ->add('imagePath', FileType::class, [
            'required' => false,
            'mapped' => false
        ]);
}
```

**Controller (`src/Controller/MoviesController.php`):**
```php
#[Route('/movies/create', name: 'movies_create')]
public function create(Request $request)
{
    $movie = new Movie();
    $form = $this->createForm(MovieFormType::class, $movie);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $newMovie = $form->getData();
        $imagePath = $form->get('imagePath')->getData();

        if ($imagePath) {
            $newFileName = uniqid() . '.' . $imagePath->guessExtension();
            try {
                $imagePath->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads',
                    $newFileName
                );
            } catch (FileException $e) {
                return new Response($e->getMessage());
            }
            $newMovie->setImagePath($newFileName);
        }

        $this->entityManager->persist($newMovie);
        $this->entityManager->flush();

        return $this->redirectToRoute('movies_index');
    }

    return $this->render('movies/create.html.twig', [
        'form' => $form->createView(),
    ]);
}
```

**Template (`templates/movies/create.html.twig`):**
```twig
{{ form_start(form) }}
    {{ form_widget(form) }}
    <button type="submit" class="uppercase mt-15 bg-blue-500 text-gray-100 text-lg font-extrabold py-4 px-8 rounded-3xl">
        Submit Post
    </button>
{{ form_end(form) }}
```

---

### ✏️ UPDATE

**Controller (`src/Controller/MoviesController.php`):**
```php
#[Route('/movies/edit/{id}', name: 'movies_edit')]
public function edit($id, Request $request): Response
{
    $repository = $this->entityManager->getRepository(Movie::class);
    $movie = $repository->find($id);
    
    $form = $this->createForm(MovieFormType::class, $movie);
    $form->handleRequest($request);
    $imagePath = $form->get('imagePath')->getData();

    if ($form->isSubmitted() && $form->isValid()) {
        if ($imagePath) {
            if ($movie->getImagePath() !== null) {
                // Handling existing image removal/replacement logic here
                $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                try {
                    $imagePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $movie->setImagePath($newFileName);
            }
        } else {
            // Update fields without changing image
            $movie->setTitle($form->get('title')->getData());
            $movie->setReleaseYear($form->get('releaseYear')->getData());
            $movie->setDescription($form->get('description')->getData());
        }
        
        $this->entityManager->flush();
        return $this->redirectToRoute('movies_edit', ['id' => $movie->getId()]);
    }

    return $this->render('movies/edit.html.twig', [
        'movie' => $movie,
        'form' => $form->createView()
    ]);
}
```

**Template (`templates/movies/edit.html.twig`):**
```twig
{{ form_start(form) }}
    {{ form_widget(form) }}
    <button type="submit" class="uppercase mt-15 bg-blue-500 text-gray-100 text-lg font-extrabold py-4 px-8 rounded-3xl">
        Submit Post
    </button>
{{ form_end(form) }}
```

---

### ❌ DELETE

**Controller (`src/Controller/MoviesController.php`):**
```php
#[Route('/movies/delete/{id}', name: 'movies_delete')]
public function delete($id) 
{
    $repository = $this->entityManager->getRepository(Movie::class);
    $movie = $repository->find($id);

    if ($movie) {
        $this->entityManager->remove($movie);
        $this->entityManager->flush();
    }

    return $this->redirectToRoute('movies_index');
}
```
