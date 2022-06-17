<?php

namespace App\Tests\Przetwarzanie;

use App\Entity\DokumentOdt;
use App\Entity\Pismo;
// use App\Repository\PismoRepository;
// use App\Service\PismoPrzetwarzanie\PismoPrzetwarzanieArgumentyInterface;
// use App\Service\PismoPrzetwarzanie\PpArgPracaRouterRepo;
use App\Service\PismoPrzetwarzanie\PismoPrzetwarzanieNoweMock;
use App\Service\PismoPrzetwarzanie\PismoPrzetwarzanieNoweWidok;
use App\Service\PismoPrzetwarzanie\PpArgPracaRouter;
use App\Service\PracaNaPlikach;
use App\Service\PracaNaPlikachMock;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Error\RuntimeError;
use App\Form\PismoType;


class PismoPrzetwarzanieNoweWidokTest extends KernelTestCase
{
    private $serwisyUstawione = false;
    private $em;
    private $rou;
    // private PismoPrzetwarzanieArgumentyInterface $argument;
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
        $this->argument['pracaRouter'] = new PpArgPracaRouter(new PracaNaPlikach(), $this->rou);
        // $this->argument['pracaMockRouter'] = new PpArgPracaRouter(new PracaNaPlikachMock(),$this->rou);
        $this->serwisyUstawione = true;
    }

    public function testSciezkiDlaStron_liczbaStron()
    {
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('maPodglad.pdf');
        $dokument->setFolderPodgladu('tests/png/');
        $przetwarzanie->setDokument($dokument);
        $dokumentWidok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $this->assertEquals(3, count($dokumentWidok->getSciezkiDlaStron()));
    }
    public function testSciezkiDlaStron_trescSciezki()
    {
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('maPodglad.pdf');
        $dokument->setFolderPodgladu('tests/png/');
        $przetwarzanie->setDokument($dokument);
        $dokumentWidok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $this->assertEquals('/pismo/nowyDokument/maPodglad.pdf/2', $dokumentWidok->getSciezkiDlaStron()[1]);
    }
    public function testSciezkiDoPodgladow_liczbaStron()
    {
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('maPodglad.pdf');
        $dokument->setFolderPodgladu('tests/png/');
        $przetwarzanie->setDokument($dokument);
        $dokumentWidok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $this->assertEquals(3, count($dokumentWidok->getSciezkiDoPodgladow()));
    }
    public function testSciezkiDoPodgladow_trescSciezki()
    {
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('maPodglad.pdf');
        $dokument->setFolderPodgladu('tests/png/');
        $przetwarzanie->setDokument($dokument);
        $dokumentWidok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $this->assertEquals('/tests/png/maPodglad/maPodglad-000002.png', $dokumentWidok->getSciezkiDoPodgladow()[1]);
    }
    public function testSciezkaDoPodgladu()
    {
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('maPodglad.pdf');
        $dokument->setFolderPodgladu('tests/png/');
        $przetwarzanie->setDokument($dokument);
        $dokumentWidok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $this->assertEquals('/tests/png/maPodglad/maPodglad-000002.png', $dokumentWidok->SciezkaDoPodgladu(2));
    }
    public function testSciezkaDoPodgladowBezFolderuGlownego()
    {
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('maPodglad.pdf');
        $dokument->setFolderPodgladu('tests/png/');
        $przetwarzanie->setDokument($dokument);
        $dokumentWidok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $this->assertEquals('maPodglad/maPodglad-000002.png', $dokumentWidok->SciezkaDoPodgladowBezFolderuGlownego(2));
    }
    public function testKtorySzablonNowyWidok_WyjatekDlaInnegoTypuPliku()
    {
        // folder->getSzablonSciezkaTuJestem()
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('maPodglad.ods'); //gdy już będzie obsługiwane to zmienić na inne rozszerzenie
        $przetwarzanie->setDokument($dokument);
        $dokumentWidok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Generowanie podglądu dla plików .ods nieobsługiwane');
        $dokumentWidok->getSzablonNowyWidok();
    }
    public function testKtorySzablonNowyWidok_DlaPdf()
    {
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('maPodglad.pdf');
        $przetwarzanie->setDokument($dokument);
        $dokumentWidok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $this->assertEquals('pismo/noweZeSkanu.html.twig', $dokumentWidok->getSzablonNowyWidok());
    }
    public function testUzupelnijDaneDlaGenerowaniaSzablonu_wyjątekBrakNumeruStrony()
    {
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('jakisDokument.pdf');
        $przetwarzanie->setDokument($dokument);
        $widok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('w parametrach do uzupełnienia brak numeru strony');
        $a = [];
        $widok->UzupelnijDaneDlaGenerowaniaSzablonu($a);
    }
    public function testUzupelnijDaneDlaGenerowaniaSzablonu_wymaganiaDlaPdf()
    {
        $parametry = ['numerStrony' => 1];
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('jakisDokument.pdf');
        $przetwarzanie->setDokument($dokument);
        $widok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $widok->UzupelnijDaneDlaGenerowaniaSzablonu($parametry);
        $this->assertTrue(array_key_exists('pismo', $parametry));
        $this->assertTrue(array_key_exists('sciezki_dla_stron', $parametry));
        $this->assertTrue(array_key_exists('sciezka_png', $parametry));
        $this->assertTrue(array_key_exists('sciezka_png_bez_fg', $parametry));
    }
    public function testUzupelnijDaneDlaGenerowaniaSzablonu_dokument()
    {
        $parametry = ['numerStrony' => 1];
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('jakisDokument.pdf');
        $przetwarzanie->setDokument($dokument);
        $widok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $widok->UzupelnijDaneDlaGenerowaniaSzablonu($parametry);
        $this->assertSame($dokument,$parametry['pismo']);
    }
    public function testUzupelnijDaneDlaGenerowaniaSzablonu_sciezkiDlaStron()
    {
        $parametry = ['numerStrony' => 1];
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('maPodglad.pdf');
        $dokument->setFolderPodgladu('tests/png/');
        $przetwarzanie->setDokument($dokument);
        $widok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $widok->UzupelnijDaneDlaGenerowaniaSzablonu($parametry);
        $this->assertSame('/pismo/nowyDokument/maPodglad.pdf/2',$parametry['sciezki_dla_stron'][1]);
    }
    public function testUzupelnijDaneDlaGenerowaniaSzablonu_SciezkaDoPodgladu()
    {
        $parametry = ['numerStrony' => 2];
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('maPodglad.pdf');
        $dokument->setFolderPodgladu('tests/png/');
        $przetwarzanie->setDokument($dokument);
        $widok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $widok->UzupelnijDaneDlaGenerowaniaSzablonu($parametry);
        $this->assertSame('/tests/png/maPodglad/maPodglad-000002.png',$parametry['sciezka_png']);
    }
    public function testUzupelnijDaneDlaGenerowaniaSzablonu_SciezkaDoPodgladowBezFolderuGlownego()
    {
        $parametry = ['numerStrony' => 2];
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new Pismo('maPodglad.pdf');
        $dokument->setFolderPodgladu('tests/png/');
        $przetwarzanie->setDokument($dokument);
        $widok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $widok->UzupelnijDaneDlaGenerowaniaSzablonu($parametry);
        $this->assertSame('maPodglad/maPodglad-000002.png',$parametry['sciezka_png_bez_fg']);
    }
    public function testRenderowanieSzablonuBezBledu()
    {
        //poniższy test działa połowicznie, nie sprawdza czy prawidłowe są parametry sciezek
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        $formFactory = $container->get("form.factory");
        $twig = $container->get('twig');

        $dokument = new Pismo('jakisDokument.pdf');
        $parametry = [
            'numerStrony' => 1,
            'form' => $formFactory->create(PismoType::class, $dokument)->createView()
        ];
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $przetwarzanie->setDokument($dokument);
        $widok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $widok->UzupelnijDaneDlaGenerowaniaSzablonu($parametry);
        $result = true;
        try {
            $response = $twig->render($widok->getSzablonNowyWidok(), $parametry);
        } catch (RuntimeError $e) {
            echo "\n" . $e->getMessage();
            $result = false;
        }
        $this->assertTrue($result);
        // $this->assertResponseIsSuccessful();
    }

    public function testKtorySzablonNowyWidok_DlaOdt()
    {
        $przetwarzanie = new PismoPrzetwarzanieNoweMock($this->argument['pracaRouter']);
        $dokument = new DokumentOdt('maPodglad.odt');
        $przetwarzanie->setDokument($dokument);
        $dokumentWidok = new PismoPrzetwarzanieNoweWidok($przetwarzanie);
        $this->assertEquals('pismo/noweOdt.html.twig', $dokumentWidok->getSzablonNowyWidok());
    }
}
