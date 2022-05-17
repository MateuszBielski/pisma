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
    private $em;
    private $rou;
    private $ustawieniaPowtarzalne = [
        'FolderDlaPlikowPodgladu' => 'jakis/folder34/',
        'DomyslnePolozeniePliku' => 'jakis/folder/',
        'SciezkaLubNazwaPliku' => 'jakas/sciezka/nazwaPliku.odt'
    ];

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
        $przetwarzanie->PrzedFormularzem();
        $this->assertTrue($pnp->WywolaneGenerujPodgladJesliNieMa());
    }
    public function testGenerujPodgladDlaDokumentu_folderPngUstawiony()
    {
        $pnp = new PracaNaPlikach();
        $przetwarzanie = new PismoPrzetwarzanieNowe($pnp, $this->rou, $this->em);
        $przetwarzanie->setParametry($this->ustawieniaPowtarzalne);
        $przetwarzanie->setFolderDlaPlikowPodgladu('jakis/ustawiony/folder');
        $przetwarzanie->PrzedFormularzem();
        $this->assertEquals('jakis/ustawiony/folder', $pnp->getFolderDlaPlikowPodgladu());
    }
}
