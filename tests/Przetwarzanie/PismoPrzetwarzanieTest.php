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
// use Symfony\Component\ErrorHandler\ThrowableUtils;

class PismoPrzetwarzanieTest extends KernelTestCase
{
    private $em;
    private $rou;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $doctrine = $this->entityManager = $kernel->getContainer()
            ->get('doctrine');

        $this->em = $doctrine->getManager();
        $this->rou = static::getContainer()->get('router');
    }


    public function testNowe_TworzenieSerwisu()
    {
        $ppn = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $this->assertTrue($ppn->Zainicjowane());
    }
    public function testNowe_NazwaBezSciezki_BrakDomyslnegoPolozenia_wyjatek()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $przetwarzanie->setSciezkaLubNazwaPliku('nazwaPliku.odt');
        $this->expectException(Exception::class);
        // $this->expectExceptionMessage('należy ustawić domyślne położenie plików');
        $przetwarzanie->PrzedFormularzem();
    }
    public function testNowe_NazwaBezSciezki_BrakDomyslnegoPolozenia_trescWyjatek()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $przetwarzanie->setSciezkaLubNazwaPliku('nazwaPliku.odt');
        // $this->expectException(Exception::class);
        $this->expectExceptionMessage('należy ustawić domyślne położenie plików');
        $przetwarzanie->PrzedFormularzem();
    }
    public function testNowe_NazwaBezSciezki_DomyslnePolozenie()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $przetwarzanie->setSciezkaLubNazwaPliku('nazwaPliku.odt');
        $przetwarzanie->setDomyslnePolozeniePliku('jakis/folder/');
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('nazwaPliku.odt', $przetwarzanie->NowyDokument()->getNazwaPliku());
    }

    public function testNowe_SciezkaWnazwie()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $przetwarzanie->setSciezkaLubNazwaPliku('jakas/sciezka/nazwaPliku.odt');
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('nazwaPliku.odt', $przetwarzanie->NowyDokument()->getNazwaPliku());
    }
    public function testNowe_SciezkaZakodowanaWnazwie()
    {
        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em);
        $przetwarzanie->setSciezkaLubNazwaPliku('jakas+sciezka+nazwaPliku.odt');
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('nazwaPliku.odt', $przetwarzanie->NowyDokument()->getNazwaPliku());
    }
    public function testZaroponujeOznaczenie()
    {
        // $pismoRepository= $this->createMock(ObjectRepository::class);
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
        $przetwarzanie->setSciezkaLubNazwaPliku('jakas/sciezka/nazwaPliku.odt');
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals($spodziewaneOznaczenie, $przetwarzanie->NowyDokument()->getOznaczenie());
    }

    public function testZaroponujeOznaczenie_BrakPoprzednich()
    {
        // $ostatniePismo = new Pismo();
        $d = new \DateTime('now');
        $aktualnyRok = $d->format('Y');
        // $ostatniePismo->setOznaczenie('L.dz. 5/' . $aktualnyRok);

        $pismoRepository = $this->createMock(PismoRepository::class);

        $pismoRepository->expects($this->any())
            ->method('OstatniNumerPrzychodzacych')
            ->willReturn(null);

        $przetwarzanie = new PismoPrzetwarzanieNowe(new PracaNaPlikach(), $this->rou, $this->em, $pismoRepository);
        $spodziewaneOznaczenie = 'L.dz. 1/' . $aktualnyRok;
        $przetwarzanie->setSciezkaLubNazwaPliku('jakas/sciezka/nazwaPliku.odt');
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals($spodziewaneOznaczenie, $przetwarzanie->NowyDokument()->getOznaczenie());
    }
    public function testUruchomienieProcesu()
    {
        $pnp = new PracaNaPlikachMock();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setSciezkaLubNazwaPliku('jakas/sciezka/nazwaPliku.odt');
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
        // $this->assertTrue($pnp->UruchomienieProcesuUstawione());
    }
    public function testGenerujPodgladDlaDokumentu_pdf()
    {
        $pnp = new PracaNaPlikachMock();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setSciezkaLubNazwaPliku('jakas/sciezka/nazwaPliku.pdf');
        $przetwarzanie->PrzedFormularzem();
        $this->assertTrue($pnp->WywolaneGenerujPodgladJesliNieMa());
    }
    // $pnp->GenerujPodgladJesliNieMaDlaPisma($this->getParameter('sciezka_do_png'),$pismo);
}
