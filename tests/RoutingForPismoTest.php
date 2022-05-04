<?php

namespace App\Tests;

use App\Entity\Pismo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RoutingForPismoTest extends KernelTestCase
{
    public function testUrlWidokNowe_PismoPodstawowe(): void
    {
        // $kernel = self::bootKernel();

        // $this->assertSame('test', $kernel->getEnvironment());
        $routerService = static::getContainer()->get('router');
        // $folderUrlNew = $routerService->generate('nazwy_folderow_dla_autocomplete',['id' => 12]);
        // $container = static::getContainer();
        $pismo = new Pismo('sciezka/do/plikNr3.pdf');
        $pismo->setRouter($routerService);
        $this->assertEquals('/pismo/noweZeSkanu/plikNr3.pdf', $pismo->UrlWidokNowe());
    }
    public function testUrlWidokNowe_NieUstawionyRouter()
    {
        $pismo = new Pismo('sciezka/do/plikNr3.pdf');
        $this->assertTrue(false);
        $this->expectedException();
    }
}
