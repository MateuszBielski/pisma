<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class FolderControllerTest extends WebTestCase
{

    private $scBezwzgl_testsController = __DIR__;

    public function testnazwyFolderowDlaAutocompleteZId(): void
    {
        $wpisana = dirname($this->scBezwzgl_testsController) . "/odczytFolderow/folder2/dal";

        $client = static::createClient();
        $crawler = $client->xmlHttpRequest(
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

        $client = static::createClient();
        $crawler = $client->xmlHttpRequest(
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

        $client = static::createClient();
        $crawler = $client->xmlHttpRequest(
            'GET',
            '/folder/nazwyFolderowDlaAutocomplete/null',
            [
                'sciezkaWpisana' => $wpisana,
                'sciezkaOdcietaDoFolderuDotychczas' => 'ostatni'
            ]
        );
        $resp = json_decode($client->getResponse()->getContent(), true);
        $this->assertMatchesRegularExpression('/<a href="\/folder\/new\/\+/', $resp['sciezkaTuJestemHtml']);
    }
    public function testZwracaSciezke_edit(): void
    {
        $wpisana = dirname($this->scBezwzgl_testsController) . "/odczytFolderow/folder2/dal";

        $client = static::createClient();
        $crawler = $client->xmlHttpRequest(
            'GET',
            '/folder/nazwyFolderowDlaAutocomplete/12',
            [
                'sciezkaWpisana' => $wpisana,
                'sciezkaOdcietaDoFolderuDotychczas' => 'ostatni'
            ]
        );
        $resp = json_decode($client->getResponse()->getContent(), true);
        $this->assertMatchesRegularExpression('/<a href="\/folder\/12\/edit\/\+/', $resp['sciezkaTuJestemHtml']);
    }
}
