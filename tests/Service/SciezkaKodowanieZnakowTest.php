<?php

namespace App\Tests\Service;

use App\Service\SciezkaKodowanieZnakow;
use PHPUnit\Framework\TestCase;

class SciezkaKodowanieZnakowTest extends TestCase
{
    public function testKodowanie_sciezkaRoot(): void
    {
        
        $kodowanie = new SciezkaKodowanieZnakow;
        $this->assertEquals("+",$kodowanie->Koduj("/"));
    }
    public function testKodowanie_sciezka1(): void
    {
        $kodowanie = new SciezkaKodowanieZnakow;
        $this->assertEquals("+jakas+inna+sciezka",$kodowanie->Koduj("/jakas/inna/sciezka"));
    }
    public function testKodowanie_sciezkaplus(): void
    {
        $kodowanie = new SciezkaKodowanieZnakow;
        $this->assertEquals("+jakas+in++na+sciezka",$kodowanie->Koduj("/jakas/in+na/sciezka"));
    }
    public function testKodowanie_sciezkaplusNaKoncu(): void
    {
        $kodowanie = new SciezkaKodowanieZnakow;
        $this->assertEquals("+jakas+inna+sciez++ka",$kodowanie->Koduj("/jakas/inna/sciez+ka"));
    }
    public function testDekodowanie()
    {
        $kodowanie = new SciezkaKodowanieZnakow;
        $this->assertEquals("/jakas/inna/sciezka",$kodowanie->Dekoduj("+jakas+inna+sciezka"));

    }
    public function testDekodowanie_plus()
    {
        $kodowanie = new SciezkaKodowanieZnakow;
        $this->assertEquals("/ja+kas/inna/sciezka",$kodowanie->Dekoduj("+ja++kas+inna+sciezka"));
        // $folder = new Folder;
    }
    public function testDekodowanie_NieZakododwane()
    {
        $kodowanie = new SciezkaKodowanieZnakow;
        $this->assertEquals("/ja+kas/inna/sciezka",$kodowanie->Dekoduj("/ja+kas/inna/sciezka"));

    }
    public function testDekodowanie_Zrozszerzeniem()
    {
        $kodowanie = new SciezkaKodowanieZnakow;
        $this->assertEquals("/ja+kas/inna/sciez.ka",$kodowanie->Dekoduj("/ja+kas/inna/sciez.ka"));
    }
}
