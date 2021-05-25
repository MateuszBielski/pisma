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
    public function testFolderZpodlgademPngWzglednieZgodnieZeZrodlem()
    {
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $this->assertEquals('png/BRN3C2AF41C02A8_006357/',$pismo->FolderZpodlgademPngWzglednieZgodnieZeZrodlem());//do ustalenia ukośniki
    }
    public function testUtworzonePokazujePolozeniePodgladu1strony_zmianaDomyslnegoFold()
    {
        // $adrZrodla = "/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf";

        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $pismo->setFolderPodgladu("rcp/");
        $this->assertEquals('/rcp/BRN3C2AF41C02A8_006357/BRN3C2AF41C02A8_006357-000001.png',$pismo->SciezkaDoPlikuPierwszejStronyDuzegoPodgladuPrzedZarejestrowaniem());
    }
    public function testFolderZpodlgademPngWzglednieZgodnieZeZrodlem_zmianaDomyslnegoFold()
    {
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $pismo->setFolderPodgladu("rcp/");
        $this->assertEquals('rcp/BRN3C2AF41C02A8_006357/',$pismo->FolderZpodlgademPngWzglednieZgodnieZeZrodlem());//do ustalenia ukośniki
    }
    public function testFolderZpodlgademPngWzglednieZgodnieZnazwaPliku()
    {
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $pismo->setNazwaPliku("BRN3C2AF41C02A8_006358.pdf");
        $pismo->setFolderPodgladu("rcp/");
        $this->assertEquals('rcp/BRN3C2AF41C02A8_006358/',$pismo->FolderZpodlgademPngWzglednieZgodnieZnazwaPliku());
    }


    public function testPismoCzyNiePosiadaPodgladu()
    {
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $this->assertFalse($pismo->JestPodgladDlaZrodla());
    }
    public function testPismoCzyPosiadaPodglad()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $pismo->setFolderPodgladu("tests/png/");
        $this->assertTrue($pismo->JestPodgladDlaZrodla());
    }
    public function testPismoIleStronPodgladu_tylkoPng()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $pismo->setFolderPodgladu("tests/png/");
        $sciezkiDoPodgladu = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem();
        $this->assertEquals(3,count($sciezkiDoPodgladu));
    }
    public function testSciezkiDoPlikuPodgladowPrzedZarejestrowaniem_bezSlashaWiodacgo()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $pismo->setFolderPodgladu("tests/png/");
        $sciezkiDoPodgladu = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem(false);
        $this->assertEquals('tests/png/maPodglad/maPodglad-000002.png',$sciezkiDoPodgladu[1]);
    }

    public function testGenerujNazwyZeSciezkamiDlaDocelowychPodgladow()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $pismo->setFolderPodgladu("tests/png/");
        $pismo->setNazwaPliku("nowaNazwa3.pdf");
        $sciezkiWygenerowane = $pismo->GenerujNazwyZeSciezkamiDlaDocelowychPodgladow();
        $this->assertEquals('tests/png/nowaNazwa3/nowaNazwa3-000002.png',$sciezkiWygenerowane[1]);

    }
    public function testGenerujNazwyDocelowychPodgladowZeSciezkamiWfolderzeZrodlowym()
    {
        //na potrzeby zmiany nazwy podglądów
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $pismo->setFolderPodgladu("tests/png/");
        $pismo->setNazwaPliku("nowaNazwa3.pdf");
        $sciezkiWygenerowane = $pismo->GenerujNazwyDocelowychPodgladowZeSciezkamiWfolderzeZrodlowym();
        $this->assertEquals('tests/png/maPodglad/nowaNazwa3-000002.png',$sciezkiWygenerowane[1]);
    }
    public function testSciezkiDoPodgladowZarejestrowanych()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/zrodlo.pdf");
        $pismo->setNazwaPliku("maPodglad.pdf");//nie ma znaczenia że folder ten sam jak dla testów podglądu dla źródła
        $pismo->setFolderPodgladu("tests/png/");
        $sciezkiDoPodgladu = $pismo->SciezkiDoPlikuPodgladowZarejestrowanych();
        
        $this->assertEquals('/tests/png/maPodglad/maPodglad-000002.png',$sciezkiDoPodgladu[1]);
    }

    public function testNazwaSkroconaZrodla()
    {
        $pismo2 = new Pismo("/skany/skan.pdf");
        $this->assertEquals('skan.pdf',$pismo2->NazwaSkroconaZrodla(2));
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $this->assertEquals('BRN3C...pdf',$pismo->NazwaSkroconaZrodla(5));
        $this->assertEquals('BRN3C2A...pdf',$pismo->NazwaSkroconaZrodla(7));
    }
    public function testSciezkiDoPlikuPodgladowZarejestrowanych_jesliNieMaPodgladu()
    {
        $pismo = new Pismo("/skany/skan.pdf");
        $sciezki = $pismo->SciezkiDoPlikuPodgladowZarejestrowanych();
        
    }
    //Jeśli nie ma podglądu
}
