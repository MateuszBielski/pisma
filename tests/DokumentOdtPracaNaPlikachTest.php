<?php

namespace App\Tests;

use App\Entity\DokumentOdt;
use App\Service\PracaNaPlikach;
use PHPUnit\Framework\TestCase;

class DokumentOdtPracaNaPlikachTest extends TestCase
{
    public function testOdtUtworzZPliku_WlasciwaKlasa(): void
    {
        $pnp = new PracaNaPlikach;
        $dokument = $pnp->UtworzPismoNaPodstawie("dokumentyOdt", "pierwszyOdt.odt");
        $this->assertInstanceOf(DokumentOdt::class, $dokument);
    }
    public function testUtworzony_znaSwojaNazwe(): void
    {
        $pnp = new PracaNaPlikach;
        $dokument = $pnp->UtworzPismoNaPodstawie("dokumentyOdt", "pierwszyOdt.odt");
        $this->assertEquals("pierwszyOdt.odt", $dokument->getNazwaPliku());
    }
}
