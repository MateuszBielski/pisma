<?php

namespace App\Tests;

use App\Entity\Sprawa;
use PHPUnit\Framework\TestCase;

class SprawaTest extends TestCase
{
    public function testSetOpis()
    {
        $sprawa = new Sprawa;
        $opis = 'jest to sprawa z testu setOpis';
        $sprawa->setOpis($opis);
        $this->assertEquals('jest to sprawa z testu setOpis',$sprawa->getOpis());
    }
    public function testUstawienieOpisuZerowejDlugosci()
    {
        $sprawa = new Sprawa;
        $sprawa->setOpis('');
        $this->assertEquals('',$sprawa->getOpis());
    }
    public function testUstawienieOpisuNull()
    {
        $sprawa = new Sprawa;
        $sprawa->setOpis(null);
        $this->assertEquals('',$sprawa->getOpis());
    }
    public function testSetOpisJesliZmieniony_nieZmienia()
    {
        $s = new Sprawa;
        $s->setOpis('opis pierwszy');
        $this->assertFalse($s->setOpisJesliZmieniony('opis pierwszy'));
    }
    public function testSetOpisJesliZmieniony_zmienia()
    {
        $s = new Sprawa;
        $s->setOpis('opis pierwszy');
        $this->assertTrue($s->setOpisJesliZmieniony('opis drugi'));
        $this->assertEquals('opis drugi',$s->getOpis());
    }
    public function testListaWyrazowDoUsuniecia()
    {
        $s = new Sprawa;
        $s->setOpis('opis pierwszy');
        $s->setOpisJesliZmieniony('inny opis');
        $doUsuniecia = $s->NiepotrzebneWyrazy();
        $this->assertEquals('opis',$doUsuniecia[0]->getWartosc());
        $this->assertEquals('pierwszy',$doUsuniecia[1]->getWartosc());
    }
    public function testSetOpis_WyrazyNalezaDoSprawy()
    {
        $s = new Sprawa;
        $s->setOpis('jakiś opis ciąg dalszy');
        foreach($s->getOpisCol() as $r)
        {
            $this->assertEquals($s,$r->getSprawa());
        }
    }
}
