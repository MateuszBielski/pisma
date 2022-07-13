<?php

namespace App\DataFixtures;

use App\Entity\DokumentOdt;
use App\Entity\Pismo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PismoFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $pismoPdfKtoreMoznaPobrac = new Pismo("tests/rozmiar/rozmiar.pdf");
        $pismoPdfKtoreMoznaPobrac->setPolozeniePoZarejestrowaniu("tests/rozmiar/");

        $dokumentOdt = new DokumentOdt("tests/dokumentyOdt/zZawartoscia.odt");
        $dir = dirname(__DIR__,2);
        $dokumentOdt->setPolozeniePoZarejestrowaniu($dir."/tests/dokumentyOdt/");

        $manager->persist($pismoPdfKtoreMoznaPobrac);
        $manager->persist($dokumentOdt);
        $manager->flush();

        //podczas realizacji polecenia: php bin/console --env=test doctrine:fixtures:load
        //usuwana jest zawartość bazy danych, ale kolejne użycie load nadaje numery id bez resetowania poprzedniego liczenia
    }
}
