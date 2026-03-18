<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MovieFixtures extends Fixture implements DependentFixtureInterface
{
  public function load(ObjectManager $manager): void
  {
    $movie1 = new Movie();
    $movie1->setTitle('Inception');
    $movie1->setReleaseYear(2010);
    $movie1->setDescription('A thief who steals corporate secrets.');
    $movie1->setImagePath('https://res.cloudinary.com/df7e20fdm/image/upload/v1736868496/v3wkykrgehd8ut3izabt.jpg');
    $movie1->addActor($this->getReference('actor_1', Actor::class));
    $movie1->addActor($this->getReference('actor_2', Actor::class));
    $manager->persist($movie1);

    $movie2 = new Movie();
    $movie2->setTitle('The Dark Knight');
    $movie2->setReleaseYear(2008);
    $movie2->setDescription('When the menace known as the Joker emerges.');
    $movie2->setImagePath('https://res.cloudinary.com/df7e20fdm/image/upload/v1740715001/opg7my9qpl7cn98vo121.jpg');
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
