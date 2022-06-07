<?php

namespace App\Tests\Service;

use App\Entity\DokumentOdt;
use App\Service\GeneratorPodgladuOdt\GeneratorPodgladuOdt;
use App\Service\SciezkaKodowanieZnakow;
use PHPUnit\Framework\TestCase;

class GeneratorPodgladuOdtTest extends TestCase
{
    public function testNieZnanyFolderPodgladu_wyjatek()
    {
        $this->expectExceptionMessage('Należy ustawić folder dla podglądu Odt');
        $generator = new GeneratorPodgladuOdt();
        $generator->Wykonaj();
    }

    public function testNieTworzyFolderu_jesliJest()
    {
        $folderPodgladOdtOgolny = 'tests/podgladDlaOdt/';
        $folderKonkretny = $folderPodgladOdtOgolny . "dlaKonkretnegoPliku/";
        $plikPodgladu = $folderKonkretny . 'dlaKonkretnegoPliku-0001.html';
        if (!is_dir($folderKonkretny)) mkdir($folderKonkretny, 0777, true);
        if (!is_file($plikPodgladu)) {
            $f = fopen($plikPodgladu, 'w');
            fclose($f);
        }

        $generator = new GeneratorPodgladuOdt();
        $generator->setParametry([
            'podgladDla' => new DokumentOdt('dlaKonkretnegoPliku.odt'),
            'folderPodgladuOdt' => $folderPodgladOdtOgolny
        ]);
        $generator->Wykonaj();
        $this->assertTrue(is_file($plikPodgladu));
    }
    public function testPowstajeFolderPodgladu()
    {
        $folderPodgladOdtOgolny = 'tests/podgladDlaOdt/';
        $folderKonkretny = $folderPodgladOdtOgolny . "dlaInnegoPliku/";
        if (is_dir($folderKonkretny)) {
            // opróżnić jeśli nie pusty
            $pliki = array_diff(scandir($folderKonkretny),['.','..']);
            foreach($pliki as $plik)unlink($folderKonkretny.$plik);
            rmdir($folderKonkretny);
        }
        $generator = new GeneratorPodgladuOdt();
        $generator->setParametry([
            'podgladDla' => new DokumentOdt('jakasSciezkaDoPliku/dlaInnegoPliku.odt'),
            'folderPodgladuOdt' => $folderPodgladOdtOgolny
        ]);
        $generator->Wykonaj();
        $this->assertTrue(is_dir($folderKonkretny));
    }
    public function testPowstajePlikHtml()
    {
        $folderPodgladOdtOgolny = 'tests/podgladDlaOdt/';
        $folderKonkretny = $folderPodgladOdtOgolny . "dlaInnego2Pliku/";
        $plikKonkretny = $folderKonkretny . 'dlaInnego2Pliku-0001.html';
        if (file_exists($plikKonkretny)) unlink($plikKonkretny);
        $generator = new GeneratorPodgladuOdt();
        $generator->setParametry([
            'podgladDla' => new DokumentOdt('jakasSciezkaDoPliku/dlaInnego2Pliku.odt'),
            'folderPodgladuOdt' => $folderPodgladOdtOgolny
        ]);
        $generator->Wykonaj();
        $this->assertTrue(file_exists($plikKonkretny));
    }
    public function testNieTworzyPlikowPodgladuJesliJest_jednostronicowy()
    {
        #
    }
    public function testNieTworzyPlikowPodgladuJesliJest_wiecejNizJednaStrona()
    {
        #
    }
}
