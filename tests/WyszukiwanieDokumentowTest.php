<?php

namespace App\Tests;

use App\Entity\Pismo;
use App\Service\WyszukiwanieDokumentow;
use DateTime;
use PHPUnit\Framework\TestCase;

class WyszukiwanieDokumentowTest extends TestCase
{
    public function testZakresDatWyszukanychDokumentow_pustyZakres()
    {
        $wd = new WyszukiwanieDokumentow;
        $wd->UstalZakresDatWyszukanychDokumentow([]);
        $this->assertEquals(null,$wd->getPoczatekData());
    }
    public function testZakresDatWyszukanychDokumentow()
    {
        $wd = new WyszukiwanieDokumentow;
        $dokumenty = [];
        $dok1 = new Pismo();
        $dok1->setDataDokumentu(new \DateTime('2019-03-05'));
        $dokumenty[] = $dok1;
        $dok2 = new Pismo();
        $dok2->setDataDokumentu(new \DateTime('2018-04-07'));
        $dokumenty[] = $dok2;
        $dok3 = new Pismo();
        $dok3->setDataDokumentu(new \DateTime('2020-02-01'));
        $dokumenty[] = $dok3;
        $wd->UstalZakresDatWyszukanychDokumentow($dokumenty);
        $this->assertEquals('2018-04-07',$wd->getPoczatekData()->format('Y-m-d'));
        $this->assertEquals('2020-02-01',$wd->getKoniecData()->format('Y-m-d'));
    }
    public function testPobierzDatyZformularzaJesliSa_nieMa()
    {
        $formularz = [];
        $wd = new WyszukiwanieDokumentow;
        $wd->setPoczatekData(new \DateTime('2013-11-11'));
        $wd->PobierzDatyZformularzaJesliSa($formularz);
        $this->assertEquals('2013-11-11',$wd->getPoczatekData()->format('Y-m-d'));
    }
    public function testPobierzDatyZformularzaJesliSa_jest()
    {
        $formularz = [];
        $formularz['poczatekData']['day'] = 4;
        $formularz['poczatekData']['month'] = 7;
        $formularz['poczatekData']['year'] = 2006;
        $formularz['koniecData']['day'] = 3;
        $formularz['koniecData']['month'] = 8;
        $formularz['koniecData']['year'] = 2016;
        $wd = new WyszukiwanieDokumentow;
        $wd->setPoczatekData(new \DateTime('2013-11-11'));
        $wd->PobierzDatyZformularzaJesliSa($formularz);
        $this->assertEquals('2006-07-04',$wd->getPoczatekData()->format('Y-m-d'));
        $this->assertEquals('2016-08-03',$wd->getKoniecData()->format('Y-m-d'));
    }
    public function testDataDlaRepo_nieUstawione()
    {
        $wd = new WyszukiwanieDokumentow;
        $this->assertEquals('',$wd->poczatekDataDlaRepo());
        $this->assertEquals('',$wd->koniecDataDlaRepo());
    }
    public function testDataDlaRepo_poczatek()
    {
        $wd = new WyszukiwanieDokumentow;
        $wd->setPoczatekData(new \DateTime('2001-01-14'));
        $this->assertEquals('2001-01-14',$wd->poczatekDataDlaRepo());
        // $this->assertEquals('',$wd->koniecDataDlaRepo());
    }
    public function testDataDlaRepo_koniec()
    {
        $wd = new WyszukiwanieDokumentow;
        $wd->setKoniecData(new \DateTime('2001-01-14'));
        $this->assertEquals('2001-01-14',$wd->koniecDataDlaRepo());
        // $this->assertEquals('',$wd->koniecDataDlaRepo());
    }
}
