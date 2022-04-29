<?php

namespace App\Tests;

use App\Entity\DokumentOdt;
use PHPUnit\Framework\TestCase;

class DokumentOdtTest extends TestCase
{
    public function testOdczytZawartosci(): void
    {
        $odt = new DokumentOdt(__DIR__."/dokumentyOdt/zZawartoscia.odt");
        $odczytanaTresc = $odt->Tresc();
        $this->assertEquals($odczytanaTresc, "testowy tekst.");
    }
}
