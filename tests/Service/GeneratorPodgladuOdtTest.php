<?php

namespace App\Tests\Service;

use App\Entity\DokumentOdt;
use App\Service\DokumentOdtMock;
use App\Service\GeneratorPodgladuOdt\GeneratorPodgladuOdt;
use App\Service\SciezkaKodowanieZnakow;
use PHPUnit\Framework\TestCase;

class GeneratorPodgladuOdtTest extends TestCase
{
    protected function OproznijIusunFolder(string $folder)
    {
        if (!is_dir($folder)) return;
        $pliki = array_diff(scandir($folder), ['.', '..']);
        foreach ($pliki as $plik) unlink($folder . $plik);
        rmdir($folder);
    }
    protected function UtworzJesliNieMaPlikZterscia($sciezkaFolder, $nazwaPliku, $tresc = '')
    {
        $folderKonkretny = $sciezkaFolder;
        $plikPodgladu = $sciezkaFolder . $nazwaPliku;
        if (!is_dir($folderKonkretny)) mkdir($folderKonkretny, 0777, true);
        if (!is_file($plikPodgladu)) {
            $f = fopen($plikPodgladu, 'w');
            if (strlen($tresc)) fwrite($f, $tresc);
            fclose($f);
        }
    }
    protected function UtworzLubNadpiszPlikZterscia($sciezkaFolder, $nazwaPliku, $tresc = '')
    {
        $folderKonkretny = $sciezkaFolder;
        $plikPodgladu = $sciezkaFolder . $nazwaPliku;
        if (!is_dir($folderKonkretny)) mkdir($folderKonkretny, 0777, true);
        $f = fopen($plikPodgladu, 'w');
        if (strlen($tresc)) fwrite($f, $tresc);
        fclose($f);
    }
    public function testOproznijNieistniejacy()
    {
        $this->OproznijIusunFolder('folder/ktorego/nieMa');
        $this->assertTrue(true);
    }
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
        $this->UtworzJesliNieMaPlikZterscia(
            $folderKonkretny,
            'dlaKonkretnegoPliku-0001.html'
        );

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
        if (is_dir($folderKonkretny)) $this->OproznijIusunFolder($folderKonkretny);
        $generator = new GeneratorPodgladuOdt();
        $generator->setParametry([
            'podgladDla' => new DokumentOdt('jakasSciezkaDoPliku/dlaInnegoPliku.odt'),
            'folderPodgladuOdt' => $folderPodgladOdtOgolny
        ]);
        $generator->Wykonaj();
        $this->assertTrue(is_dir($folderKonkretny));
    }

    public function testNieTworzyPlikowPodgladuJesliJest_jednostronicowy()
    {
        $folderPodgladOdtOgolny = 'tests/podgladDlaOdt/';
        $folderKonkretny = $folderPodgladOdtOgolny . 'juzZrobiony/';
        $nazwaPliku = 'juzZrobiony-0001.html';
        $trescNieDoZmiany = 'trescNieDoZmianyXFgl$88!~//';
        $this->UtworzLubNadpiszPlikZterscia(
            $folderKonkretny,
            $nazwaPliku,
            $trescNieDoZmiany
        );

        $generator = new GeneratorPodgladuOdt();
        $generator->setParametry([
            'podgladDla' => new DokumentOdt('jakasSciezkaDoPliku/juzZrobiony.odt'),
            'folderPodgladuOdt' => $folderPodgladOdtOgolny
        ]);
        $generator->Wykonaj();
        $sciezkaDoPliku = $folderKonkretny . $nazwaPliku;
        $plik = fopen($sciezkaDoPliku, 'r');
        $zawartosc = fread($plik, filesize($sciezkaDoPliku));
        fclose($plik);
        $this->assertSame($trescNieDoZmiany, $zawartosc);
    }
    public function testNieTworzyPlikowPodgladuJesliJest_wiecejNizJednaStrona()
    {
        #
    }
    public function testNieTworzyPlikuPodgladu_BrakTresci()
    {
        $folderPodgladOdtOgolny = 'tests/podgladDlaOdt/';
        $nazwaPliku = 'maNiebyc-0001.html';
        $sciezkaDoPliku = $folderPodgladOdtOgolny . $nazwaPliku;
        if (file_exists($sciezkaDoPliku)) unlink($sciezkaDoPliku);
        $generator = new GeneratorPodgladuOdt();
        $generator->ZapiszDoPlikuTresc($sciezkaDoPliku, '');
        $this->assertFalse(file_exists($sciezkaDoPliku));
    }
    public function testTrescDoZapisuPochodziZustawionegoDokumentu()
    {
        $dokument = new DokumentOdtMock();
        $tresc = 'jakaś konkretna treść vfl8@0-!>';
        $dokument->setTrescDoZapisu($tresc);
        //warunek wstępny:
        $this->assertSame($tresc, $dokument->Tresc());
        $generator = new GeneratorPodgladuOdt();
        $generator->setParametry([
            'podgladDla' => $dokument
        ]);
        $this->assertSame($tresc, $generator->TrescDoZapisu());
    }
    public function testTrescDoZapisu_ZapisWpliku()
    {
        $folderPodgladOdtOgolny = 'tests/podgladDlaOdt/';
        $folderKonkretny = $folderPodgladOdtOgolny . "zawartoscPliku/";
        $nazwaPliku = 'zawartoscPliku';
        $plikHtml = $folderKonkretny . $nazwaPliku . '-0001.html';
        if (file_exists($plikHtml)) {
            unlink($plikHtml);
        }

        $tresc = 'jakas tresc do zapisu %,|ss@-+~';
        $dokument = new DokumentOdtMock('sciezka/doPliku/' . $nazwaPliku . '.odt');
        $dokument->setTrescDoZapisu($tresc);

        $generator = new GeneratorPodgladuOdt();
        $generator->setParametry([
            'podgladDla' => $dokument,
            'folderPodgladuOdt' => $folderPodgladOdtOgolny
        ]);
        $generator->Wykonaj();

        $plik = fopen($plikHtml, 'r');
        $zawartosc = fread($plik, filesize($plikHtml));
        fclose($plik);
        unlink($plikHtml);
        $this->assertSame($tresc, $zawartosc);
    }
    public function testNieUstawionyDokument_Wykonaj_Wyjatek()
    {
        $generator = new GeneratorPodgladuOdt();
        $generator->setParametry([
            'folderPodgladuOdt' => 'jakis Folder'
        ]);
        $this->expectExceptionMessage('nie ustawiony dokument, nie można wykonać podglądu');
        $generator->Wykonaj();

    }
}
