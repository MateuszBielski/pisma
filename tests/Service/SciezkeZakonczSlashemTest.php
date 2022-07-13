<?php

namespace App\Tests\Service;

use App\Service\SciezkeZakonczSlashem;
use PHPUnit\Framework\TestCase;

class SciezkeZakonczSlashemTest extends TestCase
{
    public function testNieDodajeJesliSlashJest()
    {
        $sciezka = 'sciezka/zakonczona/slashem/';
        new SciezkeZakonczSlashem($sciezka);
        $this->assertSame('sciezka/zakonczona/slashem/',$sciezka);
    }
    public function testDodajeSlash()
    {
        $sciezka = 'sciezka/zakonczona/slashem';
        new SciezkeZakonczSlashem($sciezka);
        $this->assertSame('sciezka/zakonczona/slashem/',$sciezka);
    }
}
