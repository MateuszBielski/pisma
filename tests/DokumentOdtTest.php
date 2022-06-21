<?php

namespace App\Tests;

use App\Entity\DokumentOdt;
use PHPUnit\Framework\TestCase;
// use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DokumentOdtTest extends TestCase
{
    public function testOdczytZawartosci(): void
    {
        $odt = new DokumentOdt(__DIR__."/dokumentyOdt/zZawartoscia.odt");
        $odczytanaTresc = $odt->Tresc();
        $this->assertEquals($odczytanaTresc, "testowy tekst.");
    }
    public function testUzupelnijDaneDlaGenerowaniaSzablonu_sciezkaZnazwaPlikuPodgladuAktualnejStrony()
    {
        $daneDlaGenerowania = [];

        $dokument = new DokumentOdt('jakis/adres/plikZustalonymPodgladem.odt');
        $dokument->setSciezkaZnazwaPlikuPodgladuAktualnejStrony('folder/podgladuOdt/plikZustalonymPodgladem/plikZustalonymPodgladem-0001.html');
        $dokument->UzupelnijDaneDlaGenerowaniaSzablonu($daneDlaGenerowania);
        // $sciezkaPodgladu= static::getContainer()->getParameter('sciezka_do_podgladuOdt');

        $result = $daneDlaGenerowania['sciezkaZnazwaPlikuPodgladuAktualnejStrony'];
        $spodziewanaSciezkaZnazwa = "folder/podgladuOdt/plikZustalonymPodgladem/plikZustalonymPodgladem-0001.html";
        $this->assertSame($spodziewanaSciezkaZnazwa,$result);
    }

}
