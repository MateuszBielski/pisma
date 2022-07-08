<?php

namespace App\Tests\Controller;

use App\Entity\Pismo;
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

    protected function setUp(): void
    {
        if($this->systemUstawiony)return;
        $this->client = static::createClient();
        $kernel = self::bootKernel();
        $doctrine = $kernel->getContainer()
        ->get('doctrine');

        $this->entityManager = $doctrine->getManager();
        $this->repPismo = $doctrine->getRepository(Pismo::class);
        $this->systemUstawiony = true;
    }
    
    public function testNowe_Success()
    {
        // $client = static::createClient();
        $this->client->request('GET', '/pismo/nowyDokument/dok2.pdf');
        $this->assertResponseIsSuccessful();
    }
    public function testPobieranie()
    {
        $pisma = $this->repPismo->findAll();
        $ostatniePismo = end($pisma);
        $id = $ostatniePismo->getId();
        // $client = static::createClient();
        $this->client->request('GET', '/pismo/pobieranie/'.$id);
        $response = $this->client->getResponse();
        $zawartoscExpect = $response->getFile()->getContent();
        $plikPorown = new File('tests/rozmiar/rozmiar.pdf');
        $this->assertEquals($zawartoscExpect, $plikPorown->getContent());
    }
    
}
