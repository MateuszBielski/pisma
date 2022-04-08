<?php

namespace App\Tests;

use App\Entity\Pismo;
use App\Service\PracaNaPlikach;
use App\Service\UruchomienieProcesuMock;
use PHPUnit\Framework\TestCase;



class FolderPracaNaPlikachTest extends TestCase
{
    private $pathOdczytFolderow = "tests/odczytFolderow";
    private $pathDodawanieUsuwanie = "tests/dodawanieUsuwanie/"; //w aplikacji podawana jest ścieżka bezwzględna
    public function testIlePlikowWfolderze(): void
    {
        $pnp = new PracaNaPlikach();
        $nazwyFolderow = $pnp->PobierzWszystkieNazwyFolderowZfolderu($this->pathOdczytFolderow);
        $this->assertEquals(3,count($nazwyFolderow));
    }
    public function testNazwyFolderowZakonczoneUkosnikiem()
    {
        $pnp = new PracaNaPlikach();
        $nazwyFolderow = $pnp->ZfolderuPobierzNazwyFolderowZakonczoneUkosnikiem($this->pathOdczytFolderow);
        $this->assertEquals("folder1/",$nazwyFolderow[0]);
        $this->assertEquals("folder2/",$nazwyFolderow[1]);
        $this->assertEquals("folder3/",$nazwyFolderow[2]);
    }
    public function testZeSciezkiObetnijPrzedOstatnimUkosnikiem()
    {
        $pnp = new PracaNaPlikach();
        $sciezka = "/jakas/sciezka";
        $this->assertEquals("/jakas",$pnp->ZeSciezkiObetnijPrzedOstatnimUkosnikiem($sciezka));
    }
    public function testNajglebszyMozliwyFolderZniepelnejSciezki_Ukonczony()
    {
        $pnp = new PracaNaPlikach();
        $sciezkaNiepelna = "tests/odczytFolderow/folder2";
        $this->assertEquals("tests/odczytFolderow/folder2",$pnp->NajglebszyMozliwyFolderZniepelnejSciezki($sciezkaNiepelna));
    }
    public function testNajglebszyMozliwyFolderZniepelnejSciezki_Poprzedni()
    {
        $pnp = new PracaNaPlikach();
        $sciezkaNiepelna = "tests/odczytFolderow/fold";
        $this->assertEquals("tests/odczytFolderow",$pnp->NajglebszyMozliwyFolderZniepelnejSciezki($sciezkaNiepelna));
    }
    public function testNajglebszyMozliwyFolderZniepelnejSciezki_Pierwszy()
    {
        $pnp = new PracaNaPlikach();
        $sciezkaNiepelna = "/hom";
        $this->assertEquals("/",$pnp->NajglebszyMozliwyFolderZniepelnejSciezki($sciezkaNiepelna));
    }
    public function testCzescSciezkiZaFolderem()
    {
        $pnp = new PracaNaPlikach();
        $sciezkaNiepelna = "tests/odczytFolderow/fold";
        $sciezkaOstatniegoFolderu = "tests/odczytFolderow";
        $reszta = $pnp->CzescSciezkiZaFolderem($sciezkaNiepelna,$sciezkaOstatniegoFolderu);
        $this->assertEquals("/fold",$reszta);
    }
    public function testCzescSciezkiZaFolderem_katalogGlowny()
    {
        $pnp = new PracaNaPlikach();
        $sciezkaNiepelna = "/hom";
        $sciezkaOstatniegoFolderu = "/";
        $reszta = $pnp->CzescSciezkiZaFolderem($sciezkaNiepelna,$sciezkaOstatniegoFolderu);
        $this->assertEquals("/hom",$reszta);
    }
    public function testFitrujFolderyPasujaceDoFrazy()
    {
        $pnp = new PracaNaPlikach();
        $folderyRozne = ["/alib","/lib32","/lib47","/li"];
        $folderyPasujace = ["/lib32","/lib47","/li"];
        $this->assertEquals($folderyPasujace,$pnp->FitrujFolderyPasujaceDoFrazy($folderyRozne,"/li"));
    }
    public function testFitrujFolderyPasujaceDoFrazy_Ukosnik()
    {
        $pnp = new PracaNaPlikach();
        $folderyRozne = ["/a","/b","lib47","li"];
        $folderyPasujace = ["/a","/b"];
        $this->assertEquals($folderyPasujace,$pnp->FitrujFolderyPasujaceDoFrazy($folderyRozne,"/"));
    }
    public function testFitrujFolderyPasujaceDoFrazy_wszystkie()
    {
        $pnp = new PracaNaPlikach();
        $folderyRozne = ["/alib","/lib32","/lib47","/li"];
        $folderyPasujace = ["/alib","/lib32","/lib47","/li"];
        $this->assertEquals($folderyPasujace,$pnp->FitrujFolderyPasujaceDoFrazy($folderyRozne,""));
    }
    public function testFitrujFolderyPasujaceDoFrazy_WielkoscLiterNiewazna()
    {
        $pnp = new PracaNaPlikach();
        $folderyRozne = ["/alib","/lIb32","/Lib47","/li"];
        $folderyPasujace = ["/lIb32","/Lib47","/li"];
        $this->assertEquals($folderyPasujace,$pnp->FitrujFolderyPasujaceDoFrazy($folderyRozne,"/li"));
    }
}