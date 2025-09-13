<?php

namespace App\DataFixtures;

use App\Entity\Park;
use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ParkFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Initialiser Faker pour générer des données réalistes
        $faker = Factory::create('fr_FR');

        // Créer 10 parcs avec une location associée
        for ($i = 0; $i < 10; $i++) {
            // Créer une nouvelle entité Location avec les champs mis à jour
            $location = new Location();
            $location->setStreet($faker->streetName());
            $location->setCity($faker->city());
            $location->setLatitude($faker->latitude());
            $location->setLongitude($faker->longitude());
            $location->setName($faker->company());

            // Persister la location avant le parc
            $manager->persist($location);

            // Créer une nouvelle entité Park
            $park = new Park();
            $park->setDescription($faker->paragraph());
            $park->setLocation($location);

            // Persister le parc
            $manager->persist($park);
        }

        // Exécuter toutes les requêtes d'insertion
        $manager->flush();
    }
}
