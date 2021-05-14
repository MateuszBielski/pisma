<?php

namespace App\Tests;

use App\Entity\Pismo;
use App\Service\PracaNaPlikach;
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
        $this->assertEquals($nazwaZrodla,$pismo->getNazwaZrodlaPrzedZarejestrowaniem());
    }
    public function testGenerujPodgladJesliNieMaDlaPisma_tworzyOdpowiedniFolder()
    {
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
    public function _testRejestrowanePrzenosiRozpoznanyPlik(Type $var = null)
    {
        $pnp = new PracaNaPlikach();
        // $pnp->
    }

    //wczytaj podgląd
    //czy wczytany podgląd
    //utworzenie folderu png jeśli nie ma
    //usuwanie(przenoszenie) folderu z podglądem po zarejestrowaniu
    //generowane podglądy powinny być w folderze obok surowych skanów, a potem przenoszone
}
