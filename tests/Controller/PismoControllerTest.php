<?php

namespace App\Tests\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PismoControllerTest extends WebTestCase
{

    private $scBezwzgl_testsController = __DIR__;

    
    public function testNowe_Success()
    {
        $client = static::createClient();
        $client->request('GET', '/pismo/noweZeSkanu/dok2.pdf');
        $this->assertResponseIsSuccessful();
    }
    
}
