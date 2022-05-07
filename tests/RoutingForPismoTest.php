<?php

namespace App\Tests;

use App\Entity\DokumentOdt;
use App\Entity\Pismo;
use App\Service\PracaNaPlikach;
use Doctrine\Common\Cache\Psr6\InvalidArgument;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RoutingForPismoTest extends KernelTestCase
{
    public function testUrlWidokNowe_PismoPodstawowe(): void
    {
        $routerService = static::getContainer()->get('router');
        $pismo = new Pismo('sciezka/do/plikNr3.pdf');
        $pismo->setRouter($routerService);
        $this->assertEquals('/pismo/noweZeSkanu/plikNr3.pdf', $pismo->UrlWidokNowe());
    }
    public function testUrlWidokNowe_NieUstawionyRouter()
    {
        $pismo = new Pismo('sciezka/do/plikNr3.pdf');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('należy ustawić router dla pisma');
        $pismo->UrlWidokNowe();
    }
    public function testUrlWidokNowe_DokumentOdt(): void
    {
        $routerService = static::getContainer()->get('router');
        $pismo = new DokumentOdt('sciezka/do/plikNr3.odt');
        $pismo->setRouter($routerService);
        $this->assertEquals('/pismo/nowyDokumentOdt/plikNr3.odt', $pismo->UrlWidokNowe());
    }
    public function testRouterUstawiaDlaTworzonychPism()
    {
        $pnp = new PracaNaPlikach(static::getContainer()->get('router'));
        // $pnp->setRouter();
        $pisma = $pnp->UtworzPismaZfolderu("tests/skanyDoTestow",'pdf');
        $this->assertEquals('/pismo/noweZeSkanu/dok1.pdf', $pisma[0]->UrlWidokNowe());
    }
    public function testRouterUstawiaDlaTworzonych_dokOdt()
    {
        $pnp = new PracaNaPlikach(static::getContainer()->get('router'));
        $pisma = $pnp->UtworzPismaZfolderu("tests/dokumentyOdt");
        $this->assertEquals('/pismo/nowyDokumentOdt/zZawartoscia.odt', $pisma[1]->UrlWidokNowe());
    }
    public function testUrlWidokNowe_NumerStrony()
    {
        $pismo = new Pismo('sciezka/do/plikNr3.pdf');       
        $pismo->setRouter(static::getContainer()->get('router'));
        $pismo->setNumerStrony(5);
        $this->assertEquals('/pismo/noweZeSkanu/plikNr3.pdf/5', $pismo->UrlWidokNowe());
    }
    public function testUrlWidokNowe_NumerStrony_DokumentOdt(): void
    {
        $routerService = static::getContainer()->get('router');
        $pismo = new DokumentOdt('sciezka/do/plikNr3.odt');
        $pismo->setRouter($routerService);
        $pismo->setNumerStrony(5);
        $this->assertEquals('/pismo/nowyDokumentOdt/plikNr3.odt/5', $pismo->UrlWidokNowe());
    }
}
