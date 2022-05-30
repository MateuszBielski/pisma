<?php

namespace App\Tests\Przetwarzanie;

// use App\Entity\DokumentOdt;
// use App\Entity\Pismo;
// use App\Service\PracaNaPlikach;
// use Doctrine\Common\Cache\Psr6\InvalidArgument;

use App\Entity\Pismo;
use App\Repository\PismoRepository;
use App\Service\PismoPrzetwarzanie\PismoPrzetwarzanieNowe;
use App\Service\PracaNaPlikach;
use App\Service\PracaNaPlikachMock;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Exception;

class PismoPrzetwarzanieTest extends KernelTestCase
{
    private $serwisyUstawione = false;
    private $em;
    private $rou;
    private $ustawieniaPowtarzalne = [
        'FolderDlaPlikowPodgladu' => 'tests/png/',
        'DomyslnePolozeniePliku' => 'jakis/folder/',
        'SciezkaLubNazwaPliku' => 'maPodglad.odt'
    ];

    protected function setUp(): void
    {
        if ($this->serwisyUstawione) return;
        $kernel = self::bootKernel();
        $doctrine = $this->entityManager = $kernel->getContainer()
            ->get('doctrine');

        $this->em = $doctrine->getManager();
        $this->rou = static::getContainer()->get('router');
        $this->serwisyUstawione = true;
    }

    public function testNowe_TworzenieSerwisu()
    {
        $parametry = [
            new PracaNaPlikach(),
            $this->rou,
            $this->em,
        ];
        $ppn = new PismoPrzetwarzanieNowe(...$parametry);
        // $ppn = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $this->assertTrue($ppn->Zainicjowane());
    }
    public function testNowe_NazwaBezSciezki_BrakDomyslnegoPolozenia_wyjatek()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $przetwarzanie->setSciezkaLubNazwaPliku('nazwaPliku.odt');
        $this->expectException(Exception::class);
        $przetwarzanie->PrzedFormularzem();
    }
    public function testNowe_NazwaBezSciezki_BrakDomyslnegoPolozenia_trescWyjatek()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $przetwarzanie->setSciezkaLubNazwaPliku('nazwaPliku.odt');
        $this->expectExceptionMessage('należy ustawić folder z dokumentami');
        $przetwarzanie->PrzedFormularzem();
    }
    public function testNowe_NazwaBezSciezki_DomyslnePolozenie()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $parametry = $this->ustawieniaPowtarzalne;
        $parametry['SciezkaLubNazwaPliku'] = 'nazwaPliku.odt';
        $przetwarzanie->setParametry($parametry);
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('nazwaPliku.odt', $przetwarzanie->NowyDokument()->getNazwaPliku());
    }

    public function testNowe_SciezkaWnazwie()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $parametry = $this->ustawieniaPowtarzalne;
        $parametry['SciezkaLubNazwaPliku'] = 'jakas/sciezka/nazwaPliku.odt';
        $przetwarzanie->setParametry($parametry);
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('nazwaPliku.odt', $przetwarzanie->NowyDokument()->getNazwaPliku());
    }
    public function testNowe_SciezkaZakodowanaWnazwie()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $parametry = $this->ustawieniaPowtarzalne;
        $parametry['SciezkaLubNazwaPliku'] = 'jakas+sciezka+nazwaPliku.odt';
        $przetwarzanie->setParametry($parametry);
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('nazwaPliku.odt', $przetwarzanie->NowyDokument()->getNazwaPliku());
    }
    public function testNowe_dokumentZnaSwojePolozenie_FolderDomyslny()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $parametry = $this->ustawieniaPowtarzalne;
        $parametry['SciezkaLubNazwaPliku'] = 'nazwaPliku.odt';
        $parametry['DomyslnePolozeniePliku'] = 'folder245/';
        $przetwarzanie->setParametry($parametry);
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('folder245/nazwaPliku.odt', $przetwarzanie->NowyDokument()->getAdresZrodlaPrzedZarejestrowaniem());
    }
    public function testNowe_dokumentZnaSwojePolozenie_FolderWnazwie()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $parametry = $this->ustawieniaPowtarzalne;
        $parametry['SciezkaLubNazwaPliku'] = 'folder245/nazwaPliku.odt';
        $parametry['DomyslnePolozeniePliku'] = '';
        $przetwarzanie->setParametry($parametry);
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('folder245/nazwaPliku.odt', $przetwarzanie->NowyDokument()->getAdresZrodlaPrzedZarejestrowaniem());
    }
    
    // getAdresZrodlaPrzedZarejestrowaniem()
    public function testZaproponujeOznaczenie()
    {
        $ostatniePismo = new Pismo();
        $d = new \DateTime('now');
        $aktualnyRok = $d->format('Y');
        $ostatniePismo->setOznaczenie('L.dz. 5/' . $aktualnyRok);

        $pismoRepository = $this->createMock(PismoRepository::class);

        $pismoRepository->expects($this->any())
            ->method('OstatniNumerPrzychodzacych')
            ->willReturn($ostatniePismo);

        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em, $pismoRepository);
        $spodziewaneOznaczenie = 'L.dz. 6/' . $aktualnyRok;
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals($spodziewaneOznaczenie, $przetwarzanie->NowyDokument()->getOznaczenie());
    }

    public function testZaroponujeOznaczenie_BrakPoprzednich()
    {
        $d = new \DateTime('now');
        $aktualnyRok = $d->format('Y');

        $pismoRepository = $this->createMock(PismoRepository::class);

        $pismoRepository->expects($this->any())
            ->method('OstatniNumerPrzychodzacych')
            ->willReturn(null);

        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em, $pismoRepository);
        $spodziewaneOznaczenie = 'L.dz. 1/' . $aktualnyRok;
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals($spodziewaneOznaczenie, $przetwarzanie->NowyDokument()->getOznaczenie());
    }
    public function testUruchomienieProcesu()
    {
        $pnp = new PracaNaPlikachMock();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->PrzedFormularzem();
        $this->assertTrue($pnp->UruchomienieProcesuUstawione());
    }
    public function testGenerujPodgladDlaDokumentu_WyjateknieobslugiwanyFormat()
    {
        $pnp = new PracaNaPlikachMock();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setSciezkaLubNazwaPliku('jakas/sciezka/nazwaPliku.jar');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Generowanie podglądu dla plików .jar nieobsługiwane');
        $przetwarzanie->PrzedFormularzem();
    }
    public function testGenerujPodgladDlaDokumentu_pdf()
    {
        $pnp = new PracaNaPlikachMock();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->setSciezkaLubNazwaPliku('maPodglad.pdf');
        $przetwarzanie->PrzedFormularzem();
        $this->assertTrue($pnp->WywolaneGenerujPodgladJesliNieMa());
    }
    
    public function testNieWywolujeGenerujPodglad_Png_dlaOdt()
    {
        $pnp = new PracaNaPlikachMock();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->setSciezkaLubNazwaPliku('maPodglad.odt');
        $przetwarzanie->PrzedFormularzem();
        $this->assertFalse($pnp->WywolaneGenerujPodgladJesliNieMa());
    }
    
    public function testGenerujPodgladDlaDokumentu_folderPngUstawiony()
    {
        $pnp = new PracaNaPlikach();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->setSciezkaLubNazwaPliku('maPodglad.pdf');
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('tests/png/', $pnp->getFolderDlaPlikowPodgladu());
    }
    public function testUtrwalPliki_nieUtrwalaJesliFormularzNieprawidlowy()
    {
        $pnp = new PracaNaPlikach();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->PrzedFormularzem();
        // $przetwarzanie->setDocelowePolozeniePliku('jakis/nieIstniejacy/folder');
        $przetwarzanie->RezultatWalidacjiFormularza(false);
        $this->assertFalse($przetwarzanie->UtrwalPliki()->czyUtrwalone());
    }
    public function testUtrwalPliki_wyjatekJezeliNieZnanyWynikFormularza()
    {
        $pnp = new PracaNaPlikach();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->PrzedFormularzem();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Nie znany wynik walidacji formularza');
        $przetwarzanie->UtrwalPliki();
    }
    public function testUtrwalPliki_niePrzenosiDokumentu()
    {
        //pozostawia w pierwotnym miejscu, bo nie ustawiony folder docelowy dla dokumentow
        //zwraca true
        $pnp = new PracaNaPlikach();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->PrzedFormularzem();

        $przetwarzanie->RezultatWalidacjiFormularza(true);
        $this->assertTrue($przetwarzanie->UtrwalPliki()->czyUtrwalone());
        $this->assertTrue(file_exists('tests/przenoszenie/pierwotnaLokalizacja/plik.pdf'));
        
    }
    public function testUtrwalPliki_niePrzenosiPodgladu()
    {
        //pozostawia w pierwotnym miejscu, bo nie ustawiony folder docelowy dla podglądów
        //zwraca true
        $pnp = new PracaNaPlikach();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->PrzedFormularzem();
        $przetwarzanie->RezultatWalidacjiFormularza(true);

        $this->assertTrue($przetwarzanie->UtrwalPliki()->czyUtrwalone());
        $this->assertTrue(file_exists('tests/przenoszenie/pierwotnaLokalizacja/plik/podglad.png'));
    }
    public function testUtrwalPliki_zwracaFalseWniepowodzeniuUtrwalenia()
    {
        //ustawić ścieżkę do folderu, który nie istnieje
        $pnp = new PracaNaPlikach();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->setDocelowePolozeniePliku('jakis/nieIstniejacy/folder');
        $przetwarzanie->RezultatWalidacjiFormularza(true);

        $this->assertFalse($przetwarzanie->UtrwalPliki()->czyUtrwalone());
        $this->assertTrue(file_exists('tests/przenoszenie/pierwotnaLokalizacja/plik/podglad.png'));
    }
    public function testUtrwalPliki_przenosiDokument()
    {
        //przenosi i zwraca true
        $przen = 'tests/przenoszenie';
        $pierw = $przen.'/pierwotnaLokalizacja';
        $docel = $przen.'/docelowaLokalizacja';
        $pierwPlik = $pierw.'/plik.pdf';
        $docelPlik = $docel.'/plik.pdf';

        if(!file_exists($pierwPlik)) throw new Exception('brak pliku w pierwotnej lokalizacji');
        $pnp = new PracaNaPlikach();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->setSciezkaLubNazwaPliku($pierwPlik);
        $przetwarzanie->setDocelowePolozeniePliku($docel);
        $przetwarzanie->PrzedFormularzem();
        $przetwarzanie->RezultatWalidacjiFormularza(true);
        
        $this->assertTrue($przetwarzanie->UtrwalPliki()->czyUtrwalone());
        $this->assertTrue(file_exists($docelPlik));
        $this->assertFalse(file_exists($pierwPlik));
        rename($docelPlik,$pierwPlik);
    }

    public function _testUtrwalPliki_przenosiPodglad()
    {
        //przenosi i zwraca true
        //podgląd musi być dostępny z poziomu folderu public
        //więc do zastanowienia co i gdzie przenosić, bo obecnie przenoszenie podglądu polega na synchronizacji nazwy folderu z nazwą dokumentu
        /*
        $przen = 'tests/przenoszenie';
        $pierw = $przen.'/pierwotnaLokalizacja/plik';
        $docel = $przen.'/docelowaLokalizacja/plik';
        $pierwPodglad = $pierw.'/podglad.png';
        $docelPodglad = $docel.'/podglad.png';

        if(!file_exists($pierwPodglad)) throw new Exception('brak pliku w pierwotnej lokalizacji');
        $pnp = new PracaNaPlikach();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->setSciezkaLubNazwaPliku($pierwPodglad);
        $przetwarzanie->setDocelowePolozeniePodgladu($docel);
        $przetwarzanie->PrzedFormularzem();
        $przetwarzanie->RezultatWalidacjiFormularza(true);
        
        $this->assertTrue($przetwarzanie->UtrwalPliki()->czyUtrwalone());
        $this->assertTrue(file_exists($docelPodglad));
        $this->assertFalse(file_exists($pierwPodglad));
        */
    }
}
