<?php

namespace App\Tests;

use App\Service\RozpoznawanieTekstu;
use PHPUnit\Framework\TestCase;

class RozpoznawanieTekstuTest extends TestCase
{
    private $folderPng = "tests/rozpoznawanie/";
    public function testWydzielZadanyObszarGrafiki(): void
    {
        $sciezkaObrazOryg = $this->folderPng."oryg.png";
        $rt = new RozpoznawanieTekstu;
        $sciezkaObrazWydzielony = $this->folderPng."wydzielony.png";
        $wyciecie = [
            'x0' => 41,
            'y0' => 98,
            'x1' => 227,
            'y1' => 119,
        ];
        $rt->WydzielFragment($sciezkaObrazOryg,$sciezkaObrazWydzielony,$wyciecie);
        $this->assertTrue(file_exists($sciezkaObrazWydzielony));
        $wymiary = getimagesize($sciezkaObrazWydzielony);
        $this->assertEquals(227-41,$wymiary[0]);
        $this->assertEquals(119-98,$wymiary[1]);
        @unlink($sciezkaObrazWydzielony);
    }
    public function testRozpoznaj()
    {
        $rt = new RozpoznawanieTekstu;
        $this->assertEquals('tekst do rozpoznania',$rt->RozpoznajTekstZpng($this->folderPng."doRozpoznania.png"));
    }
}
