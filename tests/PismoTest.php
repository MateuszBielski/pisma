<?php

namespace App\Tests;

use App\Entity\Kontrahent;
use App\Entity\Pismo;
use DateTime;
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
        $this->assertEquals(1,count($sciezki));
    }
    public function testSetNazwaPliku_jestDostepDoNazwyPlikuPrzedZmiana()
    {
        $pismo = new Pismo("/jakis/folder/staraNazwa.pdf");
        $pismo->setNazwaPliku("nowaNazwa.pdf");
        $this->assertEquals('staraNazwa.pdf',$pismo->getNazwaPlikuPrzedZmiana());
    }
    public function testUstalStroneNaPodstawieKierunku()
    {
        $pismo = new Pismo;
        $kierunek1 = 1;
        $kierunek2 = 2;
        $nadawca = new Kontrahent;
        $nadawca->setNazwa('strona');
        $odbiorca = new Kontrahent;
        $odbiorca->setNazwa('strona');
        $pismo->UstalStroneNaPodstawieKierunku($nadawca,$kierunek1);
        $this->assertEquals(null,$pismo->getOdbiorca());
        $this->assertEquals('strona',$pismo->getNadawca()->getNazwa());

        $pismo->UstalStroneNaPodstawieKierunku($odbiorca,$kierunek2);
        $this->assertEquals('strona',$pismo->getOdbiorca()->getNazwa());
        $this->assertEquals(null,$pismo->getNadawca());

    }
    public function testUstawienieOdbiorcyZerujeNadawce()
    {
        $pismo = new Pismo;
        $pismo->setNadawca(new Kontrahent);
        $pismo->setOdbiorca(new Kontrahent);
        $this->assertEquals(null,$pismo->getNadawca());
    }
    public function testUstawienieNadawcyZerujeOdbiorcy()
    {
        $pismo = new Pismo;
        $pismo->setOdbiorca(new Kontrahent);
        $pismo->setNadawca(new Kontrahent);
        $this->assertEquals(null,$pismo->getOdbiorca());
    }
    public function testKierunekJesliJestNadawca()
    {
        $pismo = new Pismo;
        $pismo->setNadawca(new Kontrahent);
        $this->assertEquals(1,$pismo->getKierunek());
    }
    public function testKierunekJesliJestOdbiorca()
    {
        $pismo = new Pismo;
        $pismo->setOdbiorca(new Kontrahent);
        $this->assertEquals(2,$pismo->getKierunek());
    }
    public function testStronaJesliJestNadawca()
    {
        $pismo = new Pismo;
        $k = new Kontrahent;
        $pismo->setNadawca($k);
        $this->assertEquals($k,$pismo->getStrona());
    }
    public function testStronaJesliJestOdbiorca()
    {
        $pismo = new Pismo;
        $k = new Kontrahent;
        $pismo->setOdbiorca($k);
        $this->assertEquals($k,$pismo->getStrona());
    }
    public function testKierunek1_ustawiaNadawce()
    {
        $pismo = new Pismo;
        $k = new Kontrahent;
        $pismo->setStrona($k);
        $pismo->setKierunek(1);
        $this->assertEquals(null,$pismo->getOdbiorca());
        $this->assertEquals($k,$pismo->getNadawca());
    }
    public function testKierunek2_ustawiaOdbiorce()
    {
        $pismo = new Pismo;
        $k = new Kontrahent;
        $pismo->setStrona($k);
        $pismo->setKierunek(2);
        $this->assertEquals(null,$pismo->getNadawca());
        $this->assertEquals($k,$pismo->getOdbiorca());
    }
    public function testUstalKierunekIstroneJesliNadawca()
    {
        $pismo = new Pismo;
        $k = new Kontrahent;
        $pismo->setNadawca($k);
        $pismo->UstalStroneIKierunek();
        $this->assertEquals(1,$pismo->getKierunek());
    }
    public function testUstalKierunekIstroneJesliOdbiorca()
    {
        $pismo = new Pismo;
        $k = new Kontrahent;
        $pismo->setOdbiorca($k);
        $pismo->UstalStroneIKierunek();
        $this->assertEquals(2,$pismo->getKierunek());
    }
    public function testKierunekOpisowo()
    {
        $pismo = new Pismo;
        $pismo->setKierunek(1);
        $this->assertEquals("przychodzące od: ",$pismo->getKierunekOpisowo());
        $pismo->setKierunek(2);
        $this->assertEquals("wychodzące do: ",$pismo->getKierunekOpisowo());
    }
    public function testDataModyfikacjiJestDataDokumentu_dlaNiezarejestrowanych()
    {
        $adresPliku = "tests/skanyDoTestow/dok2.pdf";
        $pismo = new Pismo($adresPliku);
        $dataModyfikacji = new DateTime;
        $dataModyfikacji->setTimestamp(filemtime($adresPliku));
        $this->assertEquals($dataModyfikacji,$pismo->getDataDokumentu());
    }
    public function testUstalJesliTrzebaDateDokumentuZdatyMod_nieTrzeba()
    {
        $pismo = new Pismo();
        $data = new DateTime('now');
        $pismo->setDataDokumentu($data);
        $this->assertFalse($pismo->UstalJesliTrzebaDateDokumentuZdatyMod());
    }
    public function testUstalJesliTrzebaDateDokumentuZdatyMod_trzeba()
    {
        $pismo = new Pismo();
        $nazwaPliku = "dok2.pdf";
        $folderZplikiem = "tests/skanyDoTestow/";
        $adresPliku = $folderZplikiem.$nazwaPliku;

        $pismo->setNazwaPliku($nazwaPliku);
        $pismo->setSciezkaDoFolderuPdf($folderZplikiem);
        $pismo->UstawDateDokumentuNull();
        $this->assertTrue($pismo->UstalJesliTrzebaDateDokumentuZdatyMod());

        $dataModyfikacji = new DateTime;
        $dataModyfikacji->setTimestamp(filemtime($adresPliku));
        $this->assertEquals($dataModyfikacji,$pismo->getDataDokumentu());
    }
    /*
    public function testBrakPodgladuZarejestrowanego_GenerujePodglad()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad2.pdf");
    }*/
    //jeśli zmiana nazwy pliku zarejestrowanego - zmiana pliku.pdf
    //Jeśli nie ma podglądu zrobić podgląd 
}
