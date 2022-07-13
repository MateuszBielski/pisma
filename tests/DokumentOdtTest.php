<?php

namespace App\Tests;

use App\Entity\DokumentOdt;
use PHPUnit\Framework\TestCase;
// use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DokumentOdtTest extends TestCase
{
    public function testOdczytZawartosci(): void
    {
        $odt = new DokumentOdt(__DIR__ . "/dokumentyOdt/zZawartoscia.odt");
        $odczytanaTresc = $odt->Tresc();
        $this->assertEquals($odczytanaTresc, "testowy tekst.<BR>");
    }
    public function testOdczytZawartosci_GdyKonstruktorNiePrzekazujeAdresu(): void
    {
        // $odt = new DokumentOdt(__DIR__ . "/dokumentyOdt/zZawartoscia.odt");
        $odt = new DokumentOdt();//przy odczycie z bazy danych konstruktor przekazujący adres nie jest wywoływany więc:
        $odt->setNazwaPliku("zZawartoscia.odt");
        $odt->setPolozeniePoZarejestrowaniu(__DIR__ . "/dokumentyOdt/");
        $odczytanaTresc = $odt->Tresc();
        $this->assertEquals($odczytanaTresc, "testowy tekst.<BR>");
    }
    public function testOdczytZawartosci_GdyKonstruktorNiePrzekazujeAdresu_uzupelniaBrakujacySlash(): void
    {
        // $odt = new DokumentOdt(__DIR__ . "/dokumentyOdt/zZawartoscia.odt");
        $odt = new DokumentOdt();//przy odczycie z bazy danych konstruktor przekazujący adres nie jest wywoływany więc:
        $odt->setNazwaPliku("zZawartoscia.odt");
        $odt->setPolozeniePoZarejestrowaniu(__DIR__ . "/dokumentyOdt");
        $odczytanaTresc = $odt->Tresc();
        $this->assertEquals($odczytanaTresc, "testowy tekst.<BR>");
    }
    public function testUzupelnijDaneDlaGenerowaniaSzablonuNowyWidok_sciezkaZnazwaPlikuPodgladuAktualnejStrony()
    {
        $daneDlaGenerowania = [];

        $dokument = new DokumentOdt('jakis/adres/plikZustalonymPodgladem.odt');
        $dokument->setSciezkaZnazwaPlikuPodgladuAktualnejStrony('folder/podgladuOdt/plikZustalonymPodgladem/plikZustalonymPodgladem-0001.html');
        $dokument->UzupelnijDaneDlaGenerowaniaSzablonuNoweWidok($daneDlaGenerowania);
        // $sciezkaPodgladu= static::getContainer()->getParameter('sciezka_do_podgladuOdt');

        $result = $daneDlaGenerowania['sciezkaZnazwaPlikuPodgladuAktualnejStrony'];
        $spodziewanaSciezkaZnazwa = "folder/podgladuOdt/plikZustalonymPodgladem/plikZustalonymPodgladem-0001.html";
        $this->assertSame($spodziewanaSciezkaZnazwa, $result);
    }

    public function testUzupelnijDaneDlaGenerowaniaSzablonuWidok_sciezkaZnazwaPlikuPodgladuAktualnejStrony()
    {
        $daneDlaGenerowania = [];

        $dokument = new DokumentOdt('jakis/adres/plikZustalonymPodgladem.odt');
        $dokument->setSciezkaZnazwaPlikuPodgladuAktualnejStrony('folder/podgladuOdt/plikZustalonymPodgladem/plikZustalonymPodgladem-0001.html');
        $dokument->UzupelnijDaneDlaGenerowaniaSzablonuWidok($daneDlaGenerowania);
        // $sciezkaPodgladu= static::getContainer()->getParameter('sciezka_do_podgladuOdt');

        $result = $daneDlaGenerowania['sciezkaZnazwaPlikuPodgladuAktualnejStrony'];
        $spodziewanaSciezkaZnazwa = "folder/podgladuOdt/plikZustalonymPodgladem/plikZustalonymPodgladem-0001.html";
        $this->assertSame($spodziewanaSciezkaZnazwa, $result);
    }
    public function testSzablonWidok()
    {
        $dok = new DokumentOdt('jakasNazwa.pdf');
        $this->assertEquals('pismo/showOdt.html.twig', $dok->SzablonWidok());
    }
    public function testSetFolderPodgladuUstawiaSciezkaZnazwaPlikuPodgladuAktualnejStrony()
    {
        $dok = new DokumentOdt("/var/jakas/sciezka/skany/maPodglad.pdf");
        $dok->setFolderPodgladu("/var/www/public/jakis/folder");
        $this->assertEquals('/var/www/public/jakis/folder/maPodglad/maPodglad-0001.html', $dok->getSciezkaZnazwaPlikuPodgladuAktualnejStrony());
    }
}
