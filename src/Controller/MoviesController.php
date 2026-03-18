<?php

namespace App\Controller;

use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class MoviesController extends AbstractController
{
  private $entityManager;

  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->entityManager = $entityManager;
  }

  #[Route('/movies', name: 'app_movies')]
  public function index()
  {
    //findAll() - SELECT * FROM movies;
    //find() - SELECT * from movies WHERE id = 1;
    //findBy() - SELECT * FROM movies ORDER BY id DESC;
    //findBy() - SELECT * from movies WHERE id = 1 AND title = 'The Dark Knight'
    //count() - SELECT COUNT(id) FROM movies

    $repository = $this->entityManager->getRepository(Movie::class);
    $movies = $repository->findAll();

    dd($movies);

    return $this->render('index.html.twig', [
      'movies' => $movies,
    ]);
  }
}
