<?php

namespace App\Tests;

use App\Entity\Kontrahent;
use App\Entity\Pismo;
use App\Entity\RodzajDokumentu;
use App\Entity\Sprawa;
use App\Entity\WyrazWciagu;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PismoTest extends TestCase
{
    public function testUtworzonePokazujePolozeniePodgladu1strony()
    {
        // $adrZrodla = "/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf";
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $this->assertEquals('/png/BRN3C2AF41C02A8_006357/BRN3C2AF41C02A8_006357-000001.png', $pismo->SciezkaDoPlikuPierwszejStronyDuzegoPodgladuPrzedZarejestrowaniem());
    }
    public function testFolderZpodlgademPngWzglednieZgodnieZeZrodlem()
    {
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $this->assertEquals('png/BRN3C2AF41C02A8_006357/', $pismo->FolderZpodlgademPngWzglednieZgodnieZeZrodlem()); //do ustalenia ukośniki
    }
    public function testUtworzonePokazujePolozeniePodgladu1strony_zmianaDomyslnegoFold()
    {
        // $adrZrodla = "/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf";

        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $pismo->setFolderPodgladu("rcp/");
        $this->assertEquals('/rcp/BRN3C2AF41C02A8_006357/BRN3C2AF41C02A8_006357-000001.png', $pismo->SciezkaDoPlikuPierwszejStronyDuzegoPodgladuPrzedZarejestrowaniem());
    }
    public function testFolderZpodlgademPngWzglednieZgodnieZeZrodlem_zmianaDomyslnegoFold()
    {
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $pismo->setFolderPodgladu("rcp/");
        $this->assertEquals('rcp/BRN3C2AF41C02A8_006357/', $pismo->FolderZpodlgademPngWzglednieZgodnieZeZrodlem()); //do ustalenia ukośniki
    }
    public function testFolderZpodlgademPngWzglednieZgodnieZnazwaPliku()
    {
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $pismo->setNazwaPliku("BRN3C2AF41C02A8_006358.pdf");
        $pismo->setFolderPodgladu("rcp/");
        $this->assertEquals('rcp/BRN3C2AF41C02A8_006358/', $pismo->FolderZpodlgademPngWzglednieZgodnieZnazwaPliku());
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
        $this->assertEquals(3, count($sciezkiDoPodgladu));
    }
    public function testSciezkiDoPlikuPodgladowPrzedZarejestrowaniem_bezSlashaWiodacgo()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $pismo->setFolderPodgladu("tests/png/");
        $sciezkiDoPodgladu = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem(false);
        $this->assertEquals('tests/png/maPodglad/maPodglad-000002.png', $sciezkiDoPodgladu[1]);
    }
    public function testSciezkiDoPlikuPodgladowPrzedZarejestrowaniem_nieDodajeDrugiegoSlasha()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $fold = dirname(__DIR__);
        $pismo->setFolderPodgladu($fold."/tests/png/");
        $sciezkiDoPodgladu = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem(true);
        $this->assertEquals($fold.'/tests/png/maPodglad/maPodglad-000002.png', $sciezkiDoPodgladu[1]);
    }
    public function testSciezkiDoPlikuPodgladowPrzedZarejestrowaniemBezFolderuGlownego()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $pismo->setFolderPodgladu("tests/png/");
        $sciezkiDoPodgladu = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniemBezFolderuGlownego();
        $this->assertEquals('maPodglad/maPodglad-000002.png', $sciezkiDoPodgladu[1]);
    }
    public function testGenerujNazwyZeSciezkamiDlaDocelowychPodgladow()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $pismo->setFolderPodgladu("tests/png/");
        $pismo->setNazwaPliku("nowaNazwa3.pdf");
        $sciezkiWygenerowane = $pismo->GenerujNazwyZeSciezkamiDlaDocelowychPodgladow();
        $this->assertEquals('tests/png/nowaNazwa3/nowaNazwa3-000002.png', $sciezkiWygenerowane[1]);
    }
    public function testGenerujNazwyDocelowychPodgladowZeSciezkamiWfolderzeZrodlowym()
    {
        //na potrzeby zmiany nazwy podglądów
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $pismo->setFolderPodgladu("tests/png/");
        $pismo->setNazwaPliku("nowaNazwa3.pdf");
        $sciezkiWygenerowane = $pismo->GenerujNazwyDocelowychPodgladowZeSciezkamiWfolderzeZrodlowym();
        $this->assertEquals('tests/png/maPodglad/nowaNazwa3-000002.png', $sciezkiWygenerowane[1]);
    }
    public function testSciezkiDoPodgladowZarejestrowanych()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/zrodlo.pdf");
        $pismo->setNazwaPliku("maPodglad.pdf"); //nie ma znaczenia że folder ten sam jak dla testów podglądu dla źródła
        $pismo->setFolderPodgladu("tests/png/");
        $sciezkiDoPodgladu = $pismo->SciezkiDoPlikuPodgladowZarejestrowanych();

        $this->assertEquals('/tests/png/maPodglad/maPodglad-000002.png', $sciezkiDoPodgladu[1]);
    }
    public function testSciezkiDoPodgladowZarejestrowanychBezFolderuGlownego()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/zrodlo.pdf");
        $pismo->setNazwaPliku("maPodglad.pdf"); //nie ma znaczenia że folder ten sam jak dla testów podglądu dla źródła
        $pismo->setFolderPodgladu("tests/png/");
        $sciezkiDoPodgladu = $pismo->SciezkiDoPlikuPodgladowZarejestrowanychBezFolderuGlownego();
        $this->assertEquals('maPodglad/maPodglad-000002.png', $sciezkiDoPodgladu[1]);
    }

    public function testNazwaSkroconaZrodla()
    {
        $pismo2 = new Pismo("/skany/skan.pdf");
        $this->assertEquals('skan.pdf', $pismo2->NazwaSkroconaZrodla(2));
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $this->assertEquals('BRN3C...pdf', $pismo->NazwaSkroconaZrodla(5));
        $this->assertEquals('BRN3C2A...pdf', $pismo->NazwaSkroconaZrodla(7));
    }
    public function testSciezkiDoPlikuPodgladowZarejestrowanych_jesliNieMaPodgladu()
    {
        $pismo = new Pismo("/skany/skan.pdf");
        $sciezki = $pismo->SciezkiDoPlikuPodgladowZarejestrowanych();
        $this->assertEquals(1, count($sciezki));
    }
    public function testSetNazwaPliku_jestDostepDoNazwyPlikuPrzedZmiana()
    {
        $pismo = new Pismo("/jakis/folder/staraNazwa.pdf");
        $pismo->setNazwaPliku("nowaNazwa.pdf");
        $this->assertEquals('staraNazwa.pdf', $pismo->getNazwaPlikuPrzedZmiana());
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
        $pismo->UstalStroneNaPodstawieKierunku($nadawca, $kierunek1);
        $this->assertEquals(null, $pismo->getOdbiorca());
        $this->assertEquals('strona', $pismo->getNadawca()->getNazwa());

        $pismo->UstalStroneNaPodstawieKierunku($odbiorca, $kierunek2);
        $this->assertEquals('strona', $pismo->getOdbiorca()->getNazwa());
        $this->assertEquals(null, $pismo->getNadawca());
    }
    public function testUstawienieOdbiorcyZerujeNadawce()
    {
        $pismo = new Pismo;
        $pismo->setNadawca(new Kontrahent);
        $pismo->setOdbiorca(new Kontrahent);
        $this->assertEquals(null, $pismo->getNadawca());
    }
    public function testUstawienieNadawcyZerujeOdbiorcy()
    {
        $pismo = new Pismo;
        $pismo->setOdbiorca(new Kontrahent);
        $pismo->setNadawca(new Kontrahent);
        $this->assertEquals(null, $pismo->getOdbiorca());
    }
    public function testKierunekJesliJestNadawca()
    {
        $pismo = new Pismo;
        $pismo->setNadawca(new Kontrahent);
        $this->assertEquals(1, $pismo->getKierunek());
    }
    public function testKierunekJesliJestOdbiorca()
    {
        $pismo = new Pismo;
        $pismo->setOdbiorca(new Kontrahent);
        $this->assertEquals(2, $pismo->getKierunek());
    }
    public function testStronaJesliJestNadawca()
    {
        $pismo = new Pismo;
        $k = new Kontrahent;
        $pismo->setNadawca($k);
        $this->assertEquals($k, $pismo->getStrona());
    }
    public function testStronaJesliJestOdbiorca()
    {
        $pismo = new Pismo;
        $k = new Kontrahent;
        $pismo->setOdbiorca($k);
        $this->assertEquals($k, $pismo->getStrona());
    }
    public function testKierunek1_ustawiaNadawce()
    {
        $pismo = new Pismo;
        $k = new Kontrahent;
        $pismo->setStrona($k);
        $pismo->setKierunek(1);
        $this->assertEquals(null, $pismo->getOdbiorca());
        $this->assertEquals($k, $pismo->getNadawca());
    }
    public function testKierunek2_ustawiaOdbiorce()
    {
        $pismo = new Pismo;
        $k = new Kontrahent;
        $pismo->setStrona($k);
        $pismo->setKierunek(2);
        $this->assertEquals(null, $pismo->getNadawca());
        $this->assertEquals($k, $pismo->getOdbiorca());
    }
    public function testUstalKierunekIstroneJesliNadawca()
    {
        $pismo = new Pismo;
        $k = new Kontrahent;
        $pismo->setNadawca($k);
        $pismo->UstalStroneIKierunek();
        $this->assertEquals(1, $pismo->getKierunek());
    }
    public function testUstalKierunekIstroneJesliOdbiorca()
    {
        $pismo = new Pismo;
        $k = new Kontrahent;
        $pismo->setOdbiorca($k);
        $pismo->UstalStroneIKierunek();
        $this->assertEquals(2, $pismo->getKierunek());
    }
    public function testKierunekOpisowo()
    {
        $pismo = new Pismo;
        $pismo->setKierunek(1);
        $this->assertEquals("przychodzące od: ", $pismo->getKierunekOpisowo());
        $pismo->setKierunek(2);
        $this->assertEquals("wychodzące do: ", $pismo->getKierunekOpisowo());
    }
    public function testDataModyfikacjiJestDataDokumentu_dlaNiezarejestrowanych()
    {
        $adresPliku = "tests/skanyDoTestow/dok2.pdf";
        $pismo = new Pismo($adresPliku);
        $dataModyfikacji = new DateTime;
        $dataModyfikacji->setTimestamp(filemtime($adresPliku));
        $this->assertEquals($dataModyfikacji, $pismo->getDataDokumentu());
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
        $adresPliku = $folderZplikiem . $nazwaPliku;

        $pismo->setNazwaPliku($nazwaPliku);
        $pismo->setSciezkaDoFolderuPdf($folderZplikiem);
        $pismo->UstawDateDokumentuNull();
        $this->assertTrue($pismo->UstalJesliTrzebaDateDokumentuZdatyMod());

        $dataModyfikacji = new DateTime;
        $dataModyfikacji->setTimestamp(filemtime($adresPliku));
        $this->assertEquals($dataModyfikacji, $pismo->getDataDokumentu());
    }
    public function testPrzechwycOpisyNowychsSpraw_utworzNoweSprawy()
    {
        $sprawy = ['nowy opis1', 'nowy opis2'];
        $pismo = new Pismo();
        $pismo->PrzechwycOpisyNowychsSpraw($sprawy);
        $this->assertEquals('nowy opis1', $pismo->getSprawy()[0]->getOpis());
        $this->assertEquals('nowy opis2', $pismo->getSprawy()[1]->getOpis());
    }
    public function testPrzechwycOpisyNowychsSpraw_nieTworzyDlaPustego()
    {
        $pismo = new Pismo();
        $sprawy = [];
        $pismo->PrzechwycOpisyNowychsSpraw($sprawy);
        $this->assertEquals(0, count($pismo->getSprawy()));
    }
    public function testPrzechwycOpisyNowychsSpraw_dodajeNowaTylkoDlaOpisowejWartosci()
    {
        $pismo = new Pismo();
        $sprawy = [3, 5, 'opisowa wartość'];
        $spr3 = new Sprawa;
        $spr5 = new Sprawa;
        $pismo->addSprawy($spr3);
        $pismo->addSprawy($spr5);
        $pismo->PrzechwycOpisyNowychsSpraw($sprawy);
        $this->assertEquals(3, count($pismo->getSprawy()));
    }

    public function testPrzechwycOpisyNowychsSpraw_usuwaWykorzystaneOpisy()
    {
        $pismo = new Pismo();
        $sprawy = [3, 5, 'opisowa wartość', 12, 'inny opis', 32, 24];
        $pismo->PrzechwycOpisyNowychsSpraw($sprawy);
        $this->assertEquals([3, 5, 12, 32, 24], $sprawy);
    }
    public function testUtworzIdodajNoweSprawyWgOpisow()
    {
        $pismo = new Pismo();
        $opisySpraw = ['opis jeden', 'opis dwa', 'jeszcze inny opis'];
        $pismo->UtworzIdodajNoweSprawyWgOpisow($opisySpraw);
        $this->assertEquals('opis jeden', $pismo->getSprawy()[0]->getOpis());
        $this->assertEquals('opis dwa', $pismo->getSprawy()[1]->getOpis());
        $this->assertEquals('jeszcze inny opis', $pismo->getSprawy()[2]->getOpis());
    }
    public function testUtworzIdodajNoweSprawy_NieUsuwaIstniejacych()
    {
        $pismo = new Pismo();
        $opisySpraw = ['opis jeden', 'opis dwa', 'jeszcze inny opis'];
        $pismo->addSprawy(new Sprawa);
        $pismo->UtworzIdodajNoweSprawyWgOpisow($opisySpraw);
        $this->assertEquals(4, count($pismo->getSprawy()));
    }
    public function testUtworzIustawNowyRodzaj_nieUstawiaPrzyBraku()
    {
        $pismo = new Pismo();
        $rodzaj = new RodzajDokumentu;
        $rodzaj->setNazwa('rodzajUstalony');
        $pismo->setRodzaj($rodzaj);
        $pismo->UtworzIustawNowyRodzaj('');
        $this->assertEquals('rodzajUstalony',$pismo->getRodzaj()->getNazwa());
    }
    public function testUtworzIustawNowyRodzaj()
    {
        $pismo = new Pismo();
        $rodzaj = new RodzajDokumentu;
        $rodzaj->setNazwa('rodzajUstalony');
        $pismo->setRodzaj($rodzaj);
        $pismo->UtworzIustawNowyRodzaj('rodzajZmieniony');
        $this->assertEquals('rodzajZmieniony',$pismo->getRodzaj()->getNazwa());
    }
    public function testUtworzStroneZnazwyIkierunku_nieUstawiaPrzyBraku()
    {
        $pismo = new Pismo();
        $nadawca = new Kontrahent;
        $nadawca->setNazwa('nadawcaUstalony');
        $pismo->setNadawca($nadawca);
        $pismo->UtworzStroneZnazwyIkierunku('',2);
        $this->assertEquals('nadawcaUstalony',$pismo->getNadawca()->getNazwa());
        $this->assertEquals(null,$pismo->getOdbiorca());
    }
    public function testUtworzStroneZnazwyIkierunku()
    {
        $pismo = new Pismo();
        $nadawca = new Kontrahent;
        $nadawca->setNazwa('nadawcaUstalony');
        $pismo->setNadawca($nadawca);
        $pismo->UtworzStroneZnazwyIkierunku('odbiorcaUstalony',2);
        $this->assertEquals('odbiorcaUstalony',$pismo->getOdbiorca()->getNazwa());
        $this->assertEquals(null,$pismo->getNadawca());
    }
    public function testSetOpis()
    {
        $pismo = new Pismo;
        $opis = 'jest to pismo z testu setOpis';
        $pismo->setOpis($opis);
        $this->assertEquals('jest to pismo z testu setOpis', $pismo->getOpis());
    }
    public function testUstawienieOpisuZerowejDlugosci()
    {
        $pismo = new Pismo;
        $pismo->setOpis('');
        $this->assertEquals('', $pismo->getOpis());
    }
    public function testUstawienieOpisuNull()
    {
        $pismo = new Pismo;
        $pismo->setOpis(null);
        $this->assertEquals('', $pismo->getOpis());
    }
    public function testSetOpisJesliZmieniony_nieZmienia()
    {
        $s = new Pismo;
        $s->setOpis('opis pierwszy');
        $this->assertFalse($s->setOpisJesliZmieniony('opis pierwszy'));
    }
    public function testSetOpisJesliZmieniony_zmienia()
    {
        $s = new Pismo;
        $s->setOpis('opis pierwszy');
        $this->assertTrue($s->setOpisJesliZmieniony('opis drugi'));
        $this->assertEquals('opis drugi', $s->getOpis());
    }

    public function testListaWyrazowDoUsuniecia()
    {
        $s = new Pismo;
        $s->setOpis('opis pierwszy');
        $s->setOpisJesliZmieniony('inny opis');
        $doUsuniecia = $s->NiepotrzebneWyrazy();
        $this->assertEquals('opis', $doUsuniecia[0]->getWartosc());
        $this->assertEquals('pierwszy', $doUsuniecia[1]->getWartosc());
    }
    public function testSetOpis_WyrazyNalezaDoPisma()
    {
        $s = new Pismo;
        $s->setOpis('jakiś opis ciąg dalszy');
        foreach ($s->getOpisCol() as $r) {
            $this->assertEquals($s, $r->getPismo());
        }
    }
    public function testSetOpisJesliZmieniony_niepotrzebneWyrazyNieNalezaDoPisma()
    {
        $s = new Pismo;
        $s->setOpis('opis pierwszy');
        $s->setOpisJesliZmieniony('inny opis');
        foreach ($s->NiepotrzebneWyrazy() as $n) {
            $this->assertEquals(null, $n->getPismo());
        }
    }
    public function testOpisZnazwyPliku_obcinaRozszerzenie()
    {
        $p = new Pismo;
        $p->setNazwaPliku('jakaśNazwa.pdf');
        $p->OpisZnazwyPliku();
        $this->assertEquals('jakaśNazwa', $p->getOpis());
    }
    public function testOpisZnazwyPliku_NieObcinaJesliNieMaRozsz()
    {
        $p = new Pismo;
        $p->setNazwaPliku('jakaśNazwa');
        $p->OpisZnazwyPliku();
        $this->assertEquals('jakaśNazwa', $p->getOpis());
    }
    public function testOpisZnazwyPliku_rozdzielaSpacja()
    {
        $p = new Pismo;
        $p->setNazwaPliku('jakaś Nazwa');
        $p->OpisZnazwyPliku();
        $this->assertEquals(2, count($p->getOpisCol()));
    }

    public function testOpisZnazwyPliku_rozdzielaDolnaKreska()
    {
        $p = new Pismo;
        $p->setNazwaPliku('jakaś_Inna_Nazwa');
        $p->OpisZnazwyPliku();
        $this->assertEquals(3, count($p->getOpisCol()));
    }
    public function testSetOpisUstawiaOpisCiag()
    {
        $p = new Pismo;
        $p->setOpis('nazwa pliku');
        $this->assertEquals('nazwa pliku', $p->getOpisCiag());
    }
    public function testOpisZnazwyPliku_nieUstawiaJesliOpisIstnial()
    {
        $p = new Pismo;
        $p->setNazwaPliku('nazwa pliku');
        $p->setOpis('opis1 pliku');
        $p->OpisZnazwyPliku();
        $this->assertEquals('opis1 pliku', $p->getOpis());
    }
    public function testOpisZnazwyPliku_UstawiaOpisCiag()
    {
        $p = new Pismo;
        $p->setNazwaPliku('nazwa pliku');
        $p->addOpi(new WyrazWciagu('opis1'));
        $p->addOpi(new WyrazWciagu('pliku'));
        $p->OpisZnazwyPliku();
        $this->assertEquals('opis1 pliku', $p->getOpisCiag());
    }
    public function testKonwersjaOznaczenia_dlaUzytkownika()
    {
        $pismo = new Pismo;
        $oznaczenieZbazy = '2021_00017';
        $oznaczenieUzytkownika = 'L.dz. 17/2021';
        $this->assertEquals($oznaczenieUzytkownika, $pismo->OznaczenieKonwertujDlaUzytkownika($oznaczenieZbazy));
    }
    public function testKonwersjaOznaczenia_dlaUzytkownika_bezZmiany()
    {
        $pismo = new Pismo;
        $oznaczenieUzytkownika = 'L.dz. 17/2021';
        $this->assertEquals($oznaczenieUzytkownika, $pismo->OznaczenieKonwertujDlaUzytkownika('L.dz. 17/2021'));
    }
    public function testKonwersjaOznaczenia_dlaBazy()
    {
        $pismo = new Pismo;
        $oznaczenieUzytkownika = 'L.dz. 17/2021';
        $oznaczenieDlabazy = '2021_00017';
        $this->assertEquals($oznaczenieDlabazy, $pismo->OznaczenieKonwertujDlaBazy($oznaczenieUzytkownika));
    }
    public function testZwiekszNumeracjeOznaczeniaUzytkownika()
    {
        $pismo = new Pismo;
        $oznaczenieUzytkownika = 'L.dz. 245/2023';
        $this->assertEquals('L.dz. 246/2023', $pismo->ZwiekszNumeracjeOznaczeniaUzytkownika($oznaczenieUzytkownika));
    }
    public function testZwiekszNumeracjeOznaczeniaUzytkownika_innaLiczba()
    {
        $pismo = new Pismo;
        $oznaczenieUzytkownika = 'L.dz. 245/2023';
        $this->assertEquals('L.dz. 249/2023', $pismo->ZwiekszNumeracjeOznaczeniaUzytkownika($oznaczenieUzytkownika, 4));
    }
    public function testWewnetrznieKonwertujDlaUzytkownikaIzwiekszOjeden()
    {
        $pismo = new Pismo;
        $pismo->setOznaczenie('2021_00017');
        $pismo->WewnetrznieKonwertujDlaUzytkownikaIzwiekszOjeden();
        $this->assertEquals('L.dz. 18/2021', $pismo->getOznaczenie());
    }
    public function testNaPodstawieOstatniegoZaproponujOznaczenieZaktualnymRokiem_2006()
    {
        $pismo = new Pismo;
        $pismo->setOznaczenie('2006_00078');
        $dataTeraz = new DateTime('now');
        $aktualnyRok = $dataTeraz->format('Y');
        $oznaczenieZaktualnymRokiem = 'L.dz. 1/' . $aktualnyRok;
        $this->assertEquals($oznaczenieZaktualnymRokiem, $pismo->NaPodstawieMojegoOznZaproponujOznaczenieZaktualnymRokiem());
    }
    public function testNaPodstawieOstatniegoZaproponujOznaczenieZaktualnymRokiem_aktualny()
    {
        $dataTeraz = new DateTime('now');
        $aktualnyRok = $dataTeraz->format('Y');

        $pismo = new Pismo;
        $pismo->setOznaczenie($aktualnyRok . '_00078');
        $oznaczenieZaktualnymRokiem = 'L.dz. 79/' . $aktualnyRok;
        $this->assertEquals($oznaczenieZaktualnymRokiem, $pismo->NaPodstawieMojegoOznZaproponujOznaczenieZaktualnymRokiem());
    }
    public function testGetOznaczenieUzytkownika()
    {
        $pismo = new Pismo;
        $pismo->setOznaczenie('2006_00078');
        $this->assertEquals('L.dz. 78/2006', $pismo->getOznaczenieUzytkownika());
        $pismo->setOznaczenie('L.dz. 62/2011');
        $this->assertEquals('L.dz. 62/2011', $pismo->getOznaczenieUzytkownika());
    }
    public function testSetOznaczenie_NullzamieniaNaZerowyString()
    {
        $p = new Pismo;
        $p->setOznaczenie(null);
        $this->assertEquals('', $p->getOznaczenieUzytkownika());
    }
    public function testNaPodstawieOstatniegoZaproponujOznaczenieZaktualnymRokiem_dlaNieUstawionegoPisma()
    {
        $dataTeraz = new DateTime('now');
        $aktualnyRok = $dataTeraz->format('Y');

        $pismo = new Pismo;
        $oznaczenieZaktualnymRokiem = 'L.dz. 1/' . $aktualnyRok;
        $this->assertEquals($oznaczenieZaktualnymRokiem, $pismo->NaPodstawieMojegoOznZaproponujOznaczenieZaktualnymRokiem());
    }
    public function testRozmiarDokumentu()
    {
        $sciezkaDoPismoRozmiar = "tests/dodawanieUsuwanie/dokRozmiar.txt";
        $filesystem = new Filesystem;
        if (!$filesystem->exists($sciezkaDoPismoRozmiar))
            $filesystem->appendToFile($sciezkaDoPismoRozmiar, 'jakis tekst');
        $pismo = new Pismo($sciezkaDoPismoRozmiar);
        $this->assertEquals(11, $pismo->getRozmiarDokumentu());
        $filesystem->remove($sciezkaDoPismoRozmiar);
        # code...
    }
    public function testRozmiarCzytelny_B()
    {
        $pismo = new Pismo();
        $pismo->setRozmiar(928);
        $this->assertEquals('928.0 B',$pismo->RozmiarCzytelny());
    }
    public function testRozmiarCzytelny_kiB()
    {
        $pismo = new Pismo();
        $pismo->setRozmiar(1025);
        $this->assertEquals('1.0 kiB',$pismo->RozmiarCzytelny());
    }
    public function testRozmiarCzytelny_MiB()
    {
        $pismo = new Pismo();
        $pismo->setRozmiar(2525033);
        $this->assertEquals('2.4 MiB',$pismo->RozmiarCzytelny());
    }
    public function testRozmiarOkreslPoUstaleniuPolozenia()
    {
        //w bazie Pisma mają swoje nazwy, ale nie wiedzą, gdzie znajdują się pliki
        $pismo = new Pismo();
        $pismo->setNazwaPliku('rozmiar.pdf');
        $pismo->RozmiarOkreslPoUstaleniuPolozenia('tests/rozmiar/');
        $this->assertEquals('14.7 kiB',$pismo->RozmiarCzytelny());
    }
    
    public function testSzablonNowyWidok()
    {
        $pismo = new Pismo('jakasNazwa.pdf');
        $this->assertEquals('pismo/noweZeSkanu.html.twig',$pismo->SzablonNowyWidok());
    }
    public function testNazwaIJesliTrzbaZakodowanaSciezka_nieTrzeba()
    {
        $pismo = new Pismo('sciezka/nazwaPliku.pdf');
        $this->assertEquals('nazwaPliku.pdf',$pismo->NazwaIJesliTrzbaZakodowanaSciezka());
    }
    public function testNazwaIJesliTrzbaZakodowanaSciezka_trzeba()
    {
        $pismo = new Pismo('sciezka/do/pliku/nazwaPliku.pdf');
        $pismo->DodawajDoNazwyZakodowanaSciezke();
        $this->assertEquals('sciezka+do+pliku+nazwaPliku.pdf',$pismo->NazwaIJesliTrzbaZakodowanaSciezka());
    }
    public function testSzablonWidok()
    {
        $pismo = new Pismo('jakasNazwa.pdf');
        $this->assertEquals('pismo/show.html.twig',$pismo->SzablonWidok());
    }
    public function testSetFolderPodgladu_uciecieDoSlowaPublic_nieUcinaJesliBrakWsciezce()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $pismo->setFolderPodgladu("tests/png/");
        $sciezkiDoPodgladu = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem(false);
        $this->assertEquals('tests/png/maPodglad/maPodglad-000002.png', $sciezkiDoPodgladu[1]);
    }
    public function testSetFolderPodgladu_uciecieDoSlowaPublic()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad.pdf");
        $pismo->setFolderPodgladu("/var/www/public/skany/");
        $this->assertEquals('skany/maPodglad/', $pismo->FolderZpodlgademPngWzglednieZgodnieZnazwaPliku());
    }
    public function testNazwaZrodlaBezRozszerzenia_uzywaNazwyPliku(Type $var = null)
    {
        $pismo = new Pismo("");
        $pismo->setNazwaPliku("ustalonaNazwa.xrtg");
        $this->assertSame($pismo->NazwaZrodlaBezRozszerzenia(),"ustalonaNazwa");
    }
    /*
    public function testBrakPodgladuZarejestrowanego_GenerujePodglad()
    {
        $pismo = new Pismo("/var/jakas/sciezka/skany/maPodglad2.pdf");
    }*/
    //Jeśli nie ma podglądu zrobić podgląd 
}
