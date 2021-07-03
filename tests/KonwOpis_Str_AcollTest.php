<?php

namespace App\Tests;

use App\Entity\WyrazWciagu;
use App\Service\KonwOpis_Str_Acoll;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class KonwOpis_Str_AcollTest extends TestCase
{
    public function testCollecttionToString()
    {
        $coll = new ArrayCollection();
        $coll[] = new WyrazWciagu("wyraz1");
        $coll[] = new WyrazWciagu("wyraz2");
        $coll[] = new WyrazWciagu("wyraz3");
        $konw = new KonwOpis_Str_Acoll();

        $this->assertEquals("wyraz1 wyraz2 wyraz3",$konw->Acoll_to_string($coll));
    }
    public function testStringToCollection()
    {
        $str = 'wyr1 wyr2 wyr3';
        $konw = new KonwOpis_Str_Acoll();

        $coll = $konw->String_to_collection($str);
        $num = 1;
        foreach($coll as $r)
        {
            $this->assertEquals('wyr'.$num++,$r->getWartosc());
        }
    }
    //numeracja
    public function testCollecttionToString_Numeracja()
    {
        $coll = new ArrayCollection();
        $w1 = new WyrazWciagu("wyraz3");
        $w1->setKolejnosc(3);
        $coll[] = $w1;
        $w2 = new WyrazWciagu("wyraz1");
        $w2->setKolejnosc(1);
        $coll[] = $w2;
        $w3 = new WyrazWciagu("wyraz2");
        $w3->setKolejnosc(2);
        $coll[] = $w3;
        $konw = new KonwOpis_Str_Acoll();

        $this->assertEquals("wyraz1 wyraz2 wyraz3",$konw->Acoll_to_string($coll));
    }
    public function testStringToCollection_numeracja()
    {
        $str = 'wyr1 wyr2 wyr3';
        $konw = new KonwOpis_Str_Acoll();

        $coll = $konw->String_to_collection($str);
        $num = 1;
        foreach($coll as $r)
        {
            $this->assertEquals($num++,$r->getKolejnosc());

        }
    }

}
