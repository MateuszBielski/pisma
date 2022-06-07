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
use PHPUnit\Framework\TestCase;

use Exception;

class PismoPrzetwarzaniePrzedFormTest extends KernelTestCase //musi być bo static::getContainer
{
    private $serwisyUstawione = false;
    private $em;
    private $rou;
    private array $argument;
    private $ustawieniaPowtarzalne = [
        'FolderDlaPlikowPodgladu' => 'tests/png/',
        'DomyslnePolozeniePliku' => 'jakis/folder/',
        'SciezkaLubNazwaPliku' => 'maPodglad.odt'
    ];

    protected function setUp(): void
    {
        if ($this->serwisyUstawione) return;
        $this->rou = static::getContainer()->get('router');
        $this->argument['pracaRouter'] = new PpArgPracaRouter(new PracaNaPlikach(),$this->rou);
        $this->argument['pracaMockRouter'] = new PpArgPracaRouter(new PracaNaPlikachMock(),$this->rou);
        $this->serwisyUstawione = true;
    }

    public function _testNowe_TworzenieSerwisu()//skoro serwis działa, nie trzeba tego.
    {
        $parametry = [
            new PracaNaPlikach(),
            $this->rou,
            $this->em,
        ];
        $ppn = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
        $this->assertTrue($ppn->Zainicjowane());
    }
    public function testNowe_NazwaBezSciezki_BrakDomyslnegoPolozenia_wyjatek()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
        $przetwarzanie->setSciezkaLubNazwaPliku('nazwaPliku.odt');
        $this->expectException(Exception::class);
        $przetwarzanie->PrzedFormularzem();
    }
    public function testNowe_NazwaBezSciezki_BrakDomyslnegoPolozenia_trescWyjatek()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
        $przetwarzanie->setSciezkaLubNazwaPliku('nazwaPliku.odt');
        $this->expectExceptionMessage('należy ustawić folder z dokumentami');
        $przetwarzanie->PrzedFormularzem();
    }
    public function testNowe_NazwaBezSciezki_DomyslnePolozenie()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
        $parametry = $this->ustawieniaPowtarzalne;
        $parametry['SciezkaLubNazwaPliku'] = 'nazwaPliku.odt';
        $przetwarzanie->setParametry($parametry);
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('nazwaPliku.odt', $przetwarzanie->NowyDokument()->getNazwaPliku());
    }

    public function testNowe_SciezkaWnazwie()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
        $parametry = $this->ustawieniaPowtarzalne;
        $parametry['SciezkaLubNazwaPliku'] = 'jakas/sciezka/nazwaPliku.odt';
        $przetwarzanie->setParametry($parametry);
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('nazwaPliku.odt', $przetwarzanie->NowyDokument()->getNazwaPliku());
    }
    public function testNowe_SciezkaZakodowanaWnazwie()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
        $parametry = $this->ustawieniaPowtarzalne;
        $parametry['SciezkaLubNazwaPliku'] = 'jakas+sciezka+nazwaPliku.odt';
        $przetwarzanie->setParametry($parametry);
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('nazwaPliku.odt', $przetwarzanie->NowyDokument()->getNazwaPliku());
    }
    public function testNowe_dokumentZnaSwojePolozenie_FolderDomyslny()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
        $parametry = $this->ustawieniaPowtarzalne;
        $parametry['SciezkaLubNazwaPliku'] = 'nazwaPliku.odt';
        $parametry['DomyslnePolozeniePliku'] = 'folder245/';
        $przetwarzanie->setParametry($parametry);
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('folder245/nazwaPliku.odt', $przetwarzanie->NowyDokument()->getAdresZrodlaPrzedZarejestrowaniem());
    }
    public function testNowe_dokumentZnaSwojePolozenie_FolderWnazwie()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe($this->argument['pracaRouter']);
        $parametry = $this->ustawieniaPowtarzalne;
        $parametry['SciezkaLubNazwaPliku'] = 'folder245/nazwaPliku.odt';
        $parametry['DomyslnePolozeniePliku'] = '';
        $przetwarzanie->setParametry($parametry);
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('folder245/nazwaPliku.odt', $przetwarzanie->NowyDokument()->getAdresZrodlaPrzedZarejestrowaniem());
    }
    
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

        $argumenty = new PpArgPracaRouterRepo(new PracaNaPlikach(),$this->rou,$pismoRepository);
        $przetwarzanie = new PismoPrzetwarzanieNowe($argumenty);
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

        $argumenty = new PpArgPracaRouterRepo(new PracaNaPlikach(),$this->rou,$pismoRepository);
        $przetwarzanie = new PismoPrzetwarzanieNowe($argumenty);
        $spodziewaneOznaczenie = 'L.dz. 1/' . $aktualnyRok;
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals($spodziewaneOznaczenie, $przetwarzanie->NowyDokument()->getOznaczenie());
    }
    public function testUruchomienieProcesu()
    {
        $pnp = new PracaNaPlikachMock();
        $argumenty = new PpArgPracaRouter($pnp,$this->rou);
        $przetwarzanie = new PismoPrzetwarzanieNowe($argumenty);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->PrzedFormularzem();
        $this->assertTrue($pnp->UruchomienieProcesuUstawione());
    }
    public function testGenerujPodgladDlaDokumentu_WyjateknieobslugiwanyFormat()
    {
        $pnp = new PracaNaPlikachMock();
        $argumenty = new PpArgPracaRouter($pnp,$this->rou);
        $przetwarzanie = new PismoPrzetwarzanieNowe($argumenty);
        $przetwarzanie->setSciezkaLubNazwaPliku('jakas/sciezka/nazwaPliku.jar');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Generowanie podglądu dla plików .jar nieobsługiwane');
        $przetwarzanie->PrzedFormularzem();
    }
    public function testGenerujPodgladDlaDokumentu_pdf()
    {
        $pnp = new PracaNaPlikachMock();
        $argumenty = new PpArgPracaRouter($pnp,$this->rou);
        $przetwarzanie = new PismoPrzetwarzanieNowe($argumenty);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->setSciezkaLubNazwaPliku('maPodglad.pdf');
        $przetwarzanie->PrzedFormularzem();
        $this->assertTrue($pnp->WywolaneGenerujPodgladJesliNieMa());
    }
    
    public function testNieWywolujeGenerujPodglad_Png_dlaOdt()
    {
        $pnp = new PracaNaPlikachMock();
        $argumenty = new PpArgPracaRouter($pnp,$this->rou);
        $przetwarzanie = new PismoPrzetwarzanieNowe($argumenty);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->setSciezkaLubNazwaPliku('maPodglad.odt');
        $przetwarzanie->PrzedFormularzem();
        $this->assertFalse($pnp->WywolaneGenerujPodgladJesliNieMa());
    }
    
    public function testGenerujPodgladDlaDokumentu_folderPngUstawiony()
    {
        $pnp = new PracaNaPlikach();
        $argument = new PpArgPracaRouter($pnp,$this->rou);
        $przetwarzanie = new PismoPrzetwarzanieNowe($argument);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->setSciezkaLubNazwaPliku('maPodglad.pdf');
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('tests/png/', $pnp->getFolderDlaPlikowPodgladu());
    }
    public function testGenerujPodgladDlaDokumentuOdt_WykonajWywolane()
    {
        $pnp = new PracaNaPlikach();
        $argument = new PpArgPracaRouter($pnp,$this->rou);
        $przetwarzanie = new PismoPrzetwarzanieNowe($argument);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->setSciezkaLubNazwaPliku('plikOdt.odt');
        $generator = new GeneratorPodgladuOdtMock();
        $przetwarzanie->setGeneratorPodgladuOdtZamiastDomyslnego($generator);

        $przetwarzanie->PrzedFormularzem();
        $this->assertTrue($generator->WykonajWywolane());
    }
}
