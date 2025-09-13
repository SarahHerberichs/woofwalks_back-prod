<?php

namespace App\DataFixtures;

use App\Entity\MainPhoto;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MainPhotoFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $photo = new MainPhoto();
        $photo->setFilePath('test_photo.jpg');

        $manager->persist($photo);
        $manager->flush();

        // Référence pour pouvoir l'utiliser dans d'autres fixtures
        $this->addReference('photo-1', $photo);
    }
}