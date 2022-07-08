<?php

namespace App\Tests\Controller;

use App\Entity\Folder;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class FolderControllerTest extends WebTestCase
{

    private $scBezwzgl_testsController = __DIR__;

    private $systemUstawiony = false;
    private $entityManager;
    private $repFolder;
    private $client;

    protected function setUp(): void
    {
        if($this->systemUstawiony)return;
        $this->client = static::createClient();
        $kernel = self::bootKernel();
        $doctrine = $kernel->getContainer()
        ->get('doctrine');

        $this->entityManager = $doctrine->getManager();
        $this->repFolder = $doctrine->getRepository(Folder::class);
        $this->systemUstawiony = true;
    }

    public function testnazwyFolderowDlaAutocompleteZId(): void
    {
        $wpisana = dirname($this->scBezwzgl_testsController) . "/odczytFolderow/folder2/dal";

        $crawler = $this->client->xmlHttpRequest(
            'GET',
            '/folder/nazwyFolderowDlaAutocomplete/1',
            [
                'sciezkaWpisana' => $wpisana,
                'sciezkaOdcietaDoFolderuDotychczas' => 'ostatni'
            ]
        );

        $this->assertResponseIsSuccessful();
    }
    public function testnazwyFolderowDlaAutocompleteBezId(): void
    {
        $wpisana = dirname($this->scBezwzgl_testsController) . "/odczytFolderow/folder2/dal";

        $crawler = $this->client->xmlHttpRequest(
            'GET',
            '/folder/nazwyFolderowDlaAutocomplete/null',
            [
                'sciezkaWpisana' => $wpisana,
                'sciezkaOdcietaDoFolderuDotychczas' => 'ostatni'
            ]
        );

        $this->assertResponseIsSuccessful();
    }
    public function testZwracaSciezke_new(): void
    {
        $wpisana = dirname($this->scBezwzgl_testsController) . "/odczytFolderow/folder2/dal";

        $crawler = $this->client->xmlHttpRequest(
            'GET',
            '/folder/nazwyFolderowDlaAutocomplete/null',
            [
                'sciezkaWpisana' => $wpisana,
                'sciezkaOdcietaDoFolderuDotychczas' => 'ostatni'
            ]
        );
        $resp = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertMatchesRegularExpression('/<a href="\/folder\/new\/\+/', $resp['sciezkaTuJestemHtml']);
    }
    public function _testZwracaSciezke_edit(): void //nie działa, nie wiem jak zaślepić odczyt z bazy
    {
        $wpisana = dirname($this->scBezwzgl_testsController) . "/odczytFolderow/folder2/dal";

        $crawler = $this->client->xmlHttpRequest(
            'GET',
            '/folder/nazwyFolderowDlaAutocomplete/12',
            [
                'sciezkaWpisana' => $wpisana,
                'sciezkaOdcietaDoFolderuDotychczas' => 'ostatni'
            ]
        );
        $resp = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertMatchesRegularExpression('/<a href="\/folder\/12\/edit\/\+/', $resp['sciezkaTuJestemHtml']);
    }
    public function testShow_Success()
    {
        $foldery = $this->repFolder->findAll();
        $ostatniFolder = end($foldery);
        $id = $ostatniFolder->getId();
        
        $this->client->request('GET', '/folder/'.$id);//coś powinno być w testowej bazie
        $this->assertResponseIsSuccessful();
    }
    public function testOdczytZawartosciAjax()
    {
        $wpisana = dirname($this->scBezwzgl_testsController) . "/odczytFolderow/folder2/";
        $res = true;
        try {

            $crawler = $this->client->xmlHttpRequest(
                'GET',
                '/folder/odczytZawartosciAjax/12',
                [
                    'fraza' => $wpisana,
                    'rozmiar' => '600'
                ]
            );
        } catch (Exception $e) {
            $res = false;
        }
        $this->assertTrue($res);
        $this->assertResponseStatusCodeSame(200);
    }
}
