<?php

namespace App\DataFixtures;

use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LocationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $location = new Location();
        $location->setLatitude(48.866667);
        $location->setLongitude(2.333333);
        $location->setName('Test Location');
        $location->setCity('Paris');
        $location->setStreet('Rue de la Paix');

        $manager->persist($location);
        $manager->flush();

        // Référence pour pouvoir l'utiliser dans d'autres fixtures
        $this->addReference('location-1', $location);
    }
}