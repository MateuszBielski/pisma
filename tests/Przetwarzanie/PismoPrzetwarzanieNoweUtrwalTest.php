<?php

namespace App\Tests\Przetwarzanie;

// use App\Entity\DokumentOdt;
// use App\Entity\Pismo;
// use App\Service\PracaNaPlikach;
// use Doctrine\Common\Cache\Psr6\InvalidArgument;

use App\Entity\Pismo;
use App\Repository\PismoRepository;
use App\Service\GeneratorPodgladuOdt\GeneratorPodgladuOdtMock;
use App\Service\PismoPrzetwarzanie\PismoPrzetwarzanieArgumentyInterface;
use App\Service\PismoPrzetwarzanie\PismoPrzetwarzanieNowe;
use App\Service\PismoPrzetwarzanie\PpArgPracaRouter;
use App\Service\PismoPrzetwarzanie\PpArgPracaRouterRepo;
use App\Service\PracaNaPlikach;
use App\Service\PracaNaPlikachMock;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Exception;

class PismoPrzetwarzanieNoweUtrwalTest extends KernelTestCase
{
    private $serwisyUstawione = false;
    private $em;
    private $rou;
    // private PismoPrzetwarzanieArgumentyInterface $argument;
    private array $argument;
    private $ustawieniaPowtarzalne = [
        'FolderDlaPlikowPodgladu' => 'tests/png/',
        'DomyslnePolozeniePliku' => 'jakis/folder/',
        'SciezkaLubNazwaPliku' => 'maPodglad.odt',
        // 'FolderPodgladuDlaOdt' => 'jakisFolder/dlaOdt/',
    ];

    protected function setUp(): void
    {
        if ($this->serwisyUstawione) return;
        $this->rou = static::getContainer()->get('router');
        $this->argument['pracaRouter'] = new PpArgPracaRouter(new PracaNaPlikach(),$this->rou);
        $this->argument['pracaMockRouter'] = new PpArgPracaRouter(new PracaNaPlikachMock(),$this->rou);
        //dzięki poniższemu nie zapisują się żadne niepożądane pliki podglądu odt,
        //nie trzeba też ustawiać FolderPodgladuDlaOdt
        $this->ustawieniaPowtarzalne['GeneratorPodgladuOdtZamiastDomyslnego'] = new GeneratorPodgladuOdtMock();
        $this->serwisyUstawione = true;
    }

    public function testUtrwalPliki_nieUtrwalaJesliFormularzNieprawidlowy()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->PrzedFormularzem();
        $przetwarzanie->RezultatWalidacjiFormularza(false);
        $this->assertFalse($przetwarzanie->UtrwalPliki()->czyUtrwalone());
    }
    public function testUtrwalPliki_wyjatekJezeliNieZnanyWynikFormularza()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
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
        $przetwarzanie = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
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
        $przetwarzanie = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->PrzedFormularzem();
        $przetwarzanie->RezultatWalidacjiFormularza(true);

        $this->assertTrue($przetwarzanie->UtrwalPliki()->czyUtrwalone());
        $this->assertTrue(file_exists('tests/przenoszenie/pierwotnaLokalizacja/plik/podglad.png'));
    }
    public function testUtrwalPliki_zwracaFalseWniepowodzeniuUtrwalenia()
    {
        //ustawić ścieżkę do folderu, który nie istnieje
        $przetwarzanie = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
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
        $przetwarzanie = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
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
    public function _testSciezkiDlaStron_liczbaStron()
    {
        // $przetwarzanie = new PismoPrzetwarzanieNoweMock();
        // $dokument = new Pismo('nazwaPliku29.pdf');
        // $przetwarzanie->setDokument($dokument);
        // $dokumentWidok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        // $this->assertEquals(3,count($dokumentWidok->getSciezkiDlaStron()));
    }
}
