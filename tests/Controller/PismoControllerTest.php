<?php

namespace App\Tests\Controller;

use App\Entity\DokumentOdt;
use App\Entity\Pismo;
use App\Service\GeneratorPodgladuOdt\GeneratorPodgladuOdt;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;

class PismoControllerTest extends WebTestCase
{

    private $scBezwzgl_testsController = __DIR__;
    private $systemUstawiony = false;
    private $entityManager;
    private $repPismo;
    private $client;
    private $idPismaPdf;
    private $dokumentOdt;

    protected function setUp(): void
    {
        if ($this->systemUstawiony) return;
        $this->client = static::createClient();
        $kernel = self::bootKernel();
        $doctrine = $kernel->getContainer()
            ->get('doctrine');

        $this->entityManager = $doctrine->getManager();
        $this->repPismo = $doctrine->getRepository(Pismo::class);
        $pisma = $this->repPismo->findAll();

        foreach ($pisma as $dok) {
            if ($dok instanceof Pismo) $this->idPismaPdf = $dok->getId();
            if ($dok instanceof DokumentOdt) $this->dokumentOdt = $dok;
        }
        
        $this->systemUstawiony = true;
    }

    public function testNowe_Success()
    {
        $this->client->request('GET', '/pismo/nowyDokument/dok2.pdf');
        $this->assertResponseIsSuccessful();
    }
    public function testPobieranie()
    {

        $id = $this->idPismaPdf;
        $this->client->request('GET', '/pismo/pobieranie/' . $id);
        $response = $this->client->getResponse();
        $zawartoscExpect = $response->getFile()->getContent();
        $plikPorown = new File('tests/rozmiar/rozmiar.pdf');
        $this->assertEquals($zawartoscExpect, $plikPorown->getContent());
    }
    public function testShowPdf()
    {
        $id = $this->idPismaPdf;
        $this->client->request('GET', '/pismo/' . $id . '/1');
        $this->assertResponseIsSuccessful();
    }
    public function testShowOdt()
    {
        $generator = new GeneratorPodgladuOdt();
        $foldPodgladu = dirname(__DIR__,2).'/tests/podgladDlaOdt';
        $generator->setParametry([
            'podgladDla' => $this->dokumentOdt,
            'folderPodgladuOdt' => $foldPodgladu
        ]);
        $generator->Wykonaj();
        $this->client->request('GET', '/pismo/' . $this->dokumentOdt->getId() . '/1');
        $this->assertResponseIsSuccessful();
        $plik = $this->dokumentOdt->getSciezkaZnazwaPlikuPodgladuAktualnejStrony();
        if(file_exists($plik))unlink($plik);

    }
}
