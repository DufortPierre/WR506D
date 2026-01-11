<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Xylis\FakerCinema\Provider\Movie as MovieProvider;
use Xylis\FakerCinema\Provider\Person as PersonProvider;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        // Ajout du provider Person pour générer des acteurs
        $faker->addProvider(new PersonProvider($faker));
        
        // Génération de 190 acteurs
        $actors = $faker->actors(null, 190, false);
        $createdActors = [];
        
        foreach ($actors as $fullName) {
            $actor = new Actor();
            // Le provider Person retourne un tableau de chaînes "Prénom Nom"
            // Séparer le nom complet en firstname et lastname
            $nameParts = explode(' ', $fullName, 2);
            $actor->setFirstname($nameParts[0] ?? $faker->firstName());
            $actor->setLastname($nameParts[1] ?? $nameParts[0] ?? $faker->lastName());
            
            // Génération des autres propriétés avec Faker standard
            $actor->setBio($faker->optional()->paragraph(3));
            $actor->setDob($faker->optional(0.7)->dateTimeBetween('-80 years', '-18 years'));
            $actor->setDod($faker->optional(0.1)->dateTimeBetween($actor->getDob() ?? '-50 years', 'now'));
            $actor->setPhoto($faker->optional(0.5)->imageUrl(400, 600, 'people'));
            
            $manager->persist($actor);
            $createdActors[] = $actor;
        }
        
        $manager->flush(); // Flush pour obtenir les IDs des acteurs
        
        // Ajout du provider Movie pour générer des films
        $faker->addProvider(new MovieProvider($faker));
        $movies = $faker->movies(100);
        
        foreach ($movies as $movieTitle) {
            $movie = new Movie();
            // Le provider Movie retourne un tableau de chaînes avec les titres de films
            $movie->setName($movieTitle);
            
            // Génération des autres propriétés
            $movie->setDescription($faker->optional(0.8)->paragraph(4));
            $movie->setDuration($faker->optional()->numberBetween(80, 200));
            $movie->setReleaseDate($faker->optional(0.9)->dateTimeBetween('-30 years', 'now'));
            $movie->setImage($faker->optional(0.6)->imageUrl(800, 1200, 'movie'));
            
            // Définir aléatoirement si le film est en ligne (environ 70% en ligne)
            $movie->setOnline($faker->boolean(70));
            
            // Associer aléatoirement 1 à 5 acteurs par film
            $numActors = $faker->numberBetween(1, min(5, count($createdActors)));
            $actorsToAdd = $faker->randomElements($createdActors, $numActors);
            foreach ($actorsToAdd as $actor) {
                $movie->addActor($actor);
            }
            
            $manager->persist($movie);
        }
        
        $manager->flush();
    }
}
