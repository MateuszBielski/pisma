<?php

namespace App\Tests;

use App\Entity\Pismo;
use PHPUnit\Framework\TestCase;

class PismoTest extends TestCase
{
    public function testUtworzonePokazujePolozeniePodgladu1strony()
    {
        // $adrZrodla = "/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf";
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $this->assertEquals('/png/BRN3C2AF41C02A8_006357/BRN3C2AF41C02A8_006357-000001.png',$pismo->SciezkaDoPlikuPierwszejStronyDuzegoPodgladuPrzedZarejestrowaniem());
    }
    public function testFolderZpodlgademPngWzglednie()
    {
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $this->assertEquals('png/BRN3C2AF41C02A8_006357/',$pismo->FolderZpodlgademPngWzglednie());//do ustalenia ukośniki
    }
    public function testUtworzonePokazujePolozeniePodgladu1strony_zmianaDomyslnegoFold()
    {
        // $adrZrodla = "/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf";

        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $pismo->setFolderPodgladu("rcp/");
        $this->assertEquals('/rcp/BRN3C2AF41C02A8_006357/BRN3C2AF41C02A8_006357-000001.png',$pismo->SciezkaDoPlikuPierwszejStronyDuzegoPodgladuPrzedZarejestrowaniem());
    }
    public function testFolderZpodlgademPngWzglednie_zmianaDomyslnegoFold()
    {
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $pismo->setFolderPodgladu("rcp/");
        $this->assertEquals('rcp/BRN3C2AF41C02A8_006357/',$pismo->FolderZpodlgademPngWzglednie());//do ustalenia ukośniki
    }


    public function testPismoCzyNiePosiadaPodgladu()
    {
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $this->assertFalse($pismo->JestPodglad());
    }
    public function testPismoCzyPosiadaPodglad()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $pismo->setFolderPodgladu("tests/png/");
        $this->assertTrue($pismo->JestPodglad());
    }
    public function testPismoIleStronPodgladu_tylkoPng()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $pismo->setFolderPodgladu("tests/png/");
        $sciezkiDoPodgladu = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem();
        $this->assertEquals(3,count($sciezkiDoPodgladu));
    }
    public function testNazwaSkroconaZrodla()
    {
        $pismo2 = new Pismo("/skany/skan.pdf");
        $this->assertEquals('skan.pdf',$pismo2->NazwaSkroconaZrodla(2));
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $this->assertEquals('BRN3C...pdf',$pismo->NazwaSkroconaZrodla(5));
        $this->assertEquals('BRN3C2A...pdf',$pismo->NazwaSkroconaZrodla(7));
    }
}
