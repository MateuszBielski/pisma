<?php

namespace App\Tests;

use App\Entity\Pismo;
use App\Service\PracaNaPlikach;
use App\Service\UruchomienieProcesuMock;
use PHPUnit\Framework\TestCase;


class PismoPracaNaPlikachTest extends TestCase
{
    private $pathSkanyFolder = "tests/skanyDoTestow";
    private $pathDodawanieUsuwanie = "tests/dodawanieUsuwanie/"; //w aplikacji podawana jest ścieżka bezwzględna
    public function testIlePlikowWfolderze(): void
    {
        $pnp = new PracaNaPlikach();
        $nazwyPlikow = $pnp->PobierzWszystkieNazwyPlikowZfolderu($this->pathSkanyFolder);
        $this->assertEquals(5,count($nazwyPlikow));

    }
    public function testNazwyPlikowZrozszerzeniem()
    {
        $pnp = new PracaNaPlikach();
        $pnp->PobierzWszystkieNazwyPlikowZfolderu($this->pathSkanyFolder);
        $nazwyPlikowPdf = $pnp->NazwyZrozszerzeniem('pdf');
        // foreach($nazwy as $n)print("\n".$n);
        $this->assertEquals(3,count($nazwyPlikowPdf));
        $this->assertEquals('tests/skanyDoTestow/dok2.pdf',$nazwyPlikowPdf[1]);
    }
    public function testNazwyPlikowZrozszerzeniemBezSciezki()
    {
        $pnp = new PracaNaPlikach();
        $pnp->PobierzWszystkieNazwyPlikowZfolderu($this->pathSkanyFolder);
        $nazwyPlikowPdf = $pnp->NazwyBezSciezkiZrozszerzeniem('pdf');
        // foreach($nazwy as $n)print("\n".$n);
        $this->assertEquals(3,count($nazwyPlikowPdf));
        $this->assertEquals('dok2.pdf',$nazwyPlikowPdf[1]);
    }
    public function testUtworzPismaZfolderu()
    {
        $pnp = new PracaNaPlikach();
        $pisma = $pnp->UtworzPismaZfolderu($this->pathSkanyFolder);
        $this->assertEquals(5,count($pisma));
        $this->assertEquals('dok3.pdf',$pisma[2]->getNazwaZrodlaPrzedZarejestrowaniemObcietaSciezka());
    }
    public function testUtworzPismaZfolderu_tylkoPdf()
    {
        $pnp = new PracaNaPlikach();
        $pisma = $pnp->UtworzPismaZfolderu($this->pathSkanyFolder,'pdf');
        $this->assertEquals(3,count($pisma));
        $this->assertEquals('dok3.pdf',$pisma[2]->getNazwaZrodlaPrzedZarejestrowaniemObcietaSciezka());
    }
    public function testUtworzPismoNaPodstawie_ZnazwyObcinaSciezke()
    {
        $pnp = new PracaNaPlikach();
        $pismo = $pnp->UtworzPismoNaPodstawie("jakas/sciezka","nie/obciete/nazwa");
        $this->assertEquals("jakas/sciezka/nazwa",$pismo->getAdresZrodlaPrzedZarejestrowaniem());
    }
    public function testUtworzPismaZfolderu_bezSciezkiBezRozszerzenia()
    {
        $pnp = new PracaNaPlikach();
        $pisma = $pnp->UtworzPismaZfolderu($this->pathSkanyFolder);
        $this->assertEquals('dok3.pdf',$pisma[2]->getNazwaZrodlaPrzedZarejestrowaniemObcietaSciezka());
    }
    public function testKomunikatJezeliPustyFolder()
    {
        $pathPustyFolder = "tests/pustyFolder";
        $pnp = new PracaNaPlikach();
        $this->assertEquals('folder tests/pustyFolder jest pusty',$pnp->PobierzWszystkieNazwyPlikowZfolderu($pathPustyFolder)[0]);

    }
    public function testKomunikatJezeliBrakPlikowZrozszerzeniem()
    {
        $pnp = new PracaNaPlikach();
        $pnp->PobierzWszystkieNazwyPlikowZfolderu($this->pathSkanyFolder);
        $nazwyPlikowOps = $pnp->NazwyZrozszerzeniem('ops');
        $this->assertEquals('folder tests/skanyDoTestow nie zawiera plików .ops',$nazwyPlikowOps[0]);
    }
   
    public function testUtworzPismoNaPodstawie_NazwaZrodlaPrzedZarejestrowaniem()
    {
        $nazwaZrodla = "dok2.pdf";
        $pnp = new PracaNaPlikach;
        $pismo = $pnp->UtworzPismoNaPodstawie($this->pathSkanyFolder,$nazwaZrodla);
        $this->assertEquals($nazwaZrodla,$pismo->getNazwaZrodlaPrzedZarejestrowaniemObcietaSciezka());
    }
    public function testGenerujPodgladJesliNieMaDlaPisma_tworzyOdpowiedniFolder()
    {
        @rmdir($this->pathDodawanieUsuwanie."dok2");
        $this->assertFalse(file_exists($this->pathDodawanieUsuwanie."dok2"));
        $pnp = new PracaNaPlikach;
        $pnp->GenerujPodgladJesliNieMaDlaPisma($this->pathDodawanieUsuwanie,new Pismo("jakisFolder/dok2.pdf"));
        $this->assertTrue(file_exists($this->pathDodawanieUsuwanie."dok2"));
        rmdir($this->pathDodawanieUsuwanie."dok2");//bez tego nie przejdzie pierwsza asercja z testu

    }
    public function testGenerujPodgladJesliNieMaDlaPisma_nieTworzyFolderuJesliJuzJest()
    {
        $path = $this->pathDodawanieUsuwanie."dok3";
        if(!file_exists($path))
        mkdir($path);
        $pnp = new PracaNaPlikach;
        $pnp->GenerujPodgladJesliNieMaDlaPisma($this->pathDodawanieUsuwanie,new Pismo("jakisFolder/dok3.pdf"));
        $this->assertTrue(file_exists($path));
        rmdir($path);
    }
    public function testGenerujPodgladJesliNieMaDlaPisma_wywolanieProcesu()
    {
        $path = $this->pathDodawanieUsuwanie."dok3";
        $pnp = new PracaNaPlikach;
        $uruchomienie = new UruchomienieProcesuMock;
        $pnp->setUruchomienieProcesu($uruchomienie);
        $pnp->GenerujPodgladJesliNieMaDlaPisma($this->pathDodawanieUsuwanie,new Pismo("jakisFolder/dok3.pdf"));
        $this->assertTrue($uruchomienie->wywolanoProces);
        rmdir($path);
    }
    public function testGenerujPodgladJesliNieMaDlaPisma_argumentyPolecenia()
    {
        $path = $this->pathDodawanieUsuwanie."dok3";
        $pnp = new PracaNaPlikach;
        $uruchomienie = new UruchomienieProcesuMock;
        $pnp->setUruchomienieProcesu($uruchomienie);
        $pnp->GenerujPodgladJesliNieMaDlaPisma($this->pathDodawanieUsuwanie,new Pismo("jakis/Folder/dok3.pdf"));
        $this->assertEquals('pdftopng',$uruchomienie->argumentyPolecenia[0]);
        $this->assertEquals('jakis/Folder/dok3.pdf',$uruchomienie->argumentyPolecenia[1]);
        $this->assertEquals('png/dok3/dok3',$uruchomienie->argumentyPolecenia[2]);//obie ścieżki do sprawdzenia
        rmdir($path);
    }
    public function _testRejestrowanePrzenosiRozpoznanyPlik(Type $var = null)
    {
        $pnp = new PracaNaPlikach();
        // $pnp->
    }
    public function testGenerujPodgladJesliNieMaDlaPisma_NieWywolujeJesliJestFolderPodgladu()
    {
        $path = $this->pathDodawanieUsuwanie."dok4";
        @mkdir($path);
        $pnp = new PracaNaPlikach;
        $uruchomienie = new UruchomienieProcesuMock;
        $pnp->setUruchomienieProcesu($uruchomienie);
        $pnp->GenerujPodgladJesliNieMaDlaPisma($this->pathDodawanieUsuwanie,new Pismo("jakisFolder/dok4.pdf"));
        $this->assertFalse($uruchomienie->wywolanoProces);
        rmdir($path);
    }
    public function testGenerujPodgladJesliNieMaDlaPisma_utworzFolderRekurencyjnie()
    {
        $path1 = $this->pathDodawanieUsuwanie."posr/";
        $path2 = $path1."dok5";
        $pnp = new PracaNaPlikach;
        $pnp->GenerujPodgladJesliNieMaDlaPisma($path1,new Pismo("jakisFolder/dok5.pdf"));
        $this->assertTrue(file_exists($this->pathDodawanieUsuwanie."posr/dok5"));
        rmdir($path2);
        rmdir($path1);
    }
    public function testPrzeniesPlikiPdfiPodgladu_czyPrzenosiPlik()
    {
        $pnp = new PracaNaPlikach;
        fclose(fopen($this->pathSkanyFolder."/dok4.pdf",'w'));
        // $this->assertTrue(file_exists($this->pathSkanyFolder."/dok4.pdf"));
        $pismo = $pnp->UtworzPismoNaPodstawie($this->pathSkanyFolder,"dok4.pdf");
        $sciezkaDoZarejestrowanych = "tests/dodawanieUsuwanie/";
        $pnp->PrzeniesPlikiPdfiPodgladu($sciezkaDoZarejestrowanych,$pismo);
        $this->assertTrue(file_exists($sciezkaDoZarejestrowanych."dok4.pdf"));
        unlink($sciezkaDoZarejestrowanych."dok4.pdf");

    }
    public function testPrzeniesPlikiPdfiPodgladu_zmianaNazwy_zwracaTrue()
    {
        $pnp = new PracaNaPlikach;
        fclose(fopen($this->pathSkanyFolder."/dok4.pdf",'w'));
        $pismo = $pnp->UtworzPismoNaPodstawie($this->pathSkanyFolder,"dok4.pdf");
        $pismo->setNazwaPliku("nowaNazwa.pdf");

        $sciezkaDoZarejestrowanych = "tests/dodawanieUsuwanie/";
        $this->assertTrue($pnp->PrzeniesPlikiPdfiPodgladu($sciezkaDoZarejestrowanych,$pismo));
        $this->assertTrue(file_exists($sciezkaDoZarejestrowanych."nowaNazwa.pdf"));
        unlink($sciezkaDoZarejestrowanych."nowaNazwa.pdf");

    }
    public function testPrzeniesPlikiPdfiPodgladu_zwracaFalseJesliNieMaPlikuZrodlowego()
    {
        //karta  może być otwarta długo, plik może zostać usunięty w miedzyczasie
        $pnp = new PracaNaPlikach;
        //tworzy na podstawie nieistniejącego pliku
        $pismo = $pnp->UtworzPismoNaPodstawie($this->pathSkanyFolder,"dok5.pdf");
        $sciezkaDoZarejestrowanych = "tests/dodawanieUsuwanie/";
        $this->assertFalse($pnp->PrzeniesPlikiPdfiPodgladu($sciezkaDoZarejestrowanych,$pismo));

    }
    public function testPrzeniesPlikiPdfiPodgladu_zmianaFolderuPodgladu()
    {
        $pnp = new PracaNaPlikach;
        $pathPng = "tests/png/";
        @mkdir($pathPng."maPodglad2");
        for($i = 1; $i < 4 ; $i++)
        {
            fclose(fopen($pathPng."maPodglad2/maPodglad2-00000".$i.".png",'w'));
        }
        fclose(fopen($this->pathSkanyFolder."/maPodglad2.pdf",'w'));
        $pismo = $pnp->UtworzPismoNaPodstawie($this->pathSkanyFolder,"maPodglad2.pdf");
        $pismo->setFolderPodgladu($pathPng);
        $pismo->setNazwaPliku("nowaNazwa.pdf");

        $sciezkaDoZarejestrowanych = "tests/dodawanieUsuwanie/";
        
        $pnp->PrzeniesPlikiPdfiPodgladu($sciezkaDoZarejestrowanych,$pismo);
        
        $this->assertTrue(file_exists($pathPng."nowaNazwa"));
        $this->assertEquals(3,count($pismo->SciezkiDoPlikuPodgladowZarejestrowanych()));
        for($i = 1; $i < 4 ; $i++)unlink($pathPng."nowaNazwa/nowaNazwa-00000".$i.".png");
        rmdir($pathPng."nowaNazwa");
        unlink($sciezkaDoZarejestrowanych."nowaNazwa.pdf");
    }
    public function testUaktualnijNazwyPlikowPodgladu()
    {
        $pnp = new PracaNaPlikach;
        $pathPng = "tests/png/";
        @mkdir($pathPng."maPodglad2");
        for($i = 1; $i < 4 ; $i++)
        {
            fclose(fopen($pathPng."maPodglad2/maPodglad2-00000".$i.".png",'w'));
        }
        $sciezkaDoZarejestrowanych = "tests/dodawanieUsuwanie/";
        fclose(fopen($sciezkaDoZarejestrowanych."/maPodglad2.pdf",'w'));
        $pismo = $pnp->UtworzPismoNaPodstawie($sciezkaDoZarejestrowanych,"maPodglad2.pdf");
        $pismo->setFolderPodgladu($pathPng);
        $pismo->setNazwaPliku("nowaNazwa3.pdf");
        $this->assertEquals("maPodglad2.pdf",$pismo->getNazwaPlikuPrzedZmiana());
        $pnp->UaktualnijNazwyPlikowPodgladu($pismo);
        $path = $pathPng."nowaNazwa3";
        $this->assertEquals(3,count(array_diff(scandir($path), array('..', '.'))));
        
        for($i = 1; $i < 4 ; $i++)unlink($pathPng."nowaNazwa3/nowaNazwa3-00000".$i.".png");
        rmdir($pathPng."nowaNazwa3");
        unlink($sciezkaDoZarejestrowanych."maPodglad2.pdf");
        // $sciezkaDoZarejestrowanych = "tests/dodawanieUsuwanie/";


    }
    public function testUaktualnijNazwePlikuPdf()
    {
        
        $pismo = new Pismo;
        $nazwaPliku1 = "plikPrzedZmiana.pdf";
        $nazwaPliku2 =  "plikPoZmianie.pdf";
        $folderZplikiem = "tests/pustyFolder/";
        
        fclose(fopen($folderZplikiem.$nazwaPliku1,'w'));
        
        $pismo->setNazwaPliku($nazwaPliku1);
        $pismo->setNazwaPliku($nazwaPliku2);
        
        $pnp = new PracaNaPlikach;
        $pnp->UaktualnijNazwePlikuPdf($folderZplikiem,$pismo);

        $this->assertTrue(file_exists($folderZplikiem.$nazwaPliku2));
        unlink($folderZplikiem.$nazwaPliku2);//
    }
    
    //jeśli pusty folder surowychPDF
    //utworzenie folderu png jeśli nie ma
    
}
