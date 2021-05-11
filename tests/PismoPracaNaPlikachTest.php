<?php

namespace App\Tests;

use App\Service\PracaNaPlikach;
use PHPUnit\Framework\TestCase;

class PismoPracaNaPlikachTest extends TestCase
{
    private $pathFolder = "tests/skanyDoTestow";
    public function testIlePlikowWfolderze(): void
    {
        $pnp = new PracaNaPlikach();
        $nazwyPlikow = $pnp->PobierzWszystkieNazwyPlikowZfolderu($this->pathFolder);
        $this->assertEquals(5,count($nazwyPlikow));

    }
    public function testNazwyPlikowZrozszerzeniem()
    {
        $pnp = new PracaNaPlikach();
        $pnp->PobierzWszystkieNazwyPlikowZfolderu($this->pathFolder);
        $nazwyPlikowPdf = $pnp->NazwyZrozszerzeniem('pdf');
        // foreach($nazwy as $n)print("\n".$n);
        $this->assertEquals(3,count($nazwyPlikowPdf));
        $this->assertEquals('tests/skanyDoTestow/dok2.pdf',$nazwyPlikowPdf[1]);
    }
}
