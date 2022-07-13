<?php

namespace App\Tests\Service;

use App\Service\SciezkePrzytnijZlewejWzglednieDo;
use PHPUnit\Framework\TestCase;

class SciezkePrzytnijTest extends TestCase
{
    public function testPrzycina()
    {
        $sciezkaDoPrzyciecia = '/jakis/dlugi/lancuch/znkakow/';
        $odciecie = 'dlugi';
        new SciezkePrzytnijZlewejWzglednieDo($sciezkaDoPrzyciecia,$odciecie);
        $this->assertSame($sciezkaDoPrzyciecia,'lancuch/znkakow/');
    }
    public function testNiePrzycinaGdyBrakFrazy()
    {
        $sciezkaDoPrzyciecia = '/jakis/dlugi/lancuch/znkakow/';
        $odciecie = 'osiem';
        new SciezkePrzytnijZlewejWzglednieDo($sciezkaDoPrzyciecia,$odciecie);
        $this->assertSame($sciezkaDoPrzyciecia,'/jakis/dlugi/lancuch/znkakow/');
    }
}
