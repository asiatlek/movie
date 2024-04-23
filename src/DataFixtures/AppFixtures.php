<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        $faker->addProvider(new \Xylis\FakerCinema\Provider\Movie($faker));

        $today = new DateTime();
        $twentyYearsAgo = (new DateTime())->modify('-20 years');

        for ($i = 0; $i < 100; $i++) {
            $movie = new Movie($faker);
            $movie->setName($faker->movie);
            $movie->setDescription($faker->overview);
            $movie->setReleaseAt((new DateTime)->setTimestamp(mt_rand($twentyYearsAgo->getTimestamp(), $today->getTimestamp())));
            $movie->setRating(mt_rand(1, 5));
            $manager->persist($movie);
        }

        $manager->flush();
    }
}
