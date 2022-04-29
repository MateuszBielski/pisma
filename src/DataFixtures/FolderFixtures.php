<?php

namespace App\DataFixtures;

use App\Entity\Folder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FolderFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        $sciezka = dirname(__DIR__,2)."/tests/odczytFolderow/folder3";
        $folder = new Folder;
        $folder->setSciezkaMoja($sciezka);
        $manager->persist($folder);

        $manager->flush();
    }
}