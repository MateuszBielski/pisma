<?php

namespace App\DataFixtures;

use App\Entity\Pismo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PismoFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $pismoKtoreMoznaPobrac = new Pismo("tests/rozmiar/rozmiar.pdf");
        $pismoKtoreMoznaPobrac->setPolozeniePoZarejestrowaniu("tests/rozmiar/");
        $manager->persist($pismoKtoreMoznaPobrac);
        $manager->flush();

        //podczas realizacji polecenia: php bin/console --env=test doctrine:fixtures:load
        //usuwana jest zawartość bazy danych, ale kolejne użycie load nadaje numery id bez resetowania poprzedniego liczenia
    }
}
