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
    public function testOnPreSubmit_pobieraZformularzaFrazyDokumentSprawaKontrahent()
    {
        $formularz = [];
        $formularz['sprawa'] = 'budowy';
        $formularz['dokument'] = 'prot odb';
        $formularz['kontrahent'] = 'an';
        $wd = new WyszukiwanieDokumentow;
        $wd->onPreSubmit($formularz);

        $this->assertEquals('prot odb',$wd->getDokument());
        $this->assertEquals('budowy',$wd->getSprawa());
        $this->assertEquals('an',$wd->getKOntrahent());
    }
    public function testOnPreSubmit_pobieraDaty()
    {
        $formularz = [];
        $formularz['poczatekData']['day'] = 4;
        $formularz['poczatekData']['month'] = 7;
        $formularz['poczatekData']['year'] = 2006;
        $formularz['koniecData']['day'] = 3;
        $formularz['koniecData']['month'] = 8;
        $formularz['koniecData']['year'] = 2016;
        $wd = new WyszukiwanieDokumentow;
        $wd->onPreSubmit($formularz);
        $this->assertEquals('2006-07-04',$wd->getPoczatekData()->format('Y-m-d'));
        $this->assertEquals('2016-08-03',$wd->getKoniecData()->format('Y-m-d'));
    }
    public function testOnPreSubmit_ustalaWyszukanychPism()
    {
        $p1 = new Pismo;
        $p1->setDataDokumentu(new \DateTime('2019-02-23'));
        $p2 = new Pismo;
        $p2->setDataDokumentu(new \DateTime('2017-04-13'));
        $formularz = [];

        $wd = new WyszukiwanieDokumentow;
        $wd->UstawWyszukaneDokumenty([$p1,$p2]);
        $wd->onPreSubmit($formularz);

        $this->assertEquals('2017-04-13',$wd->getPoczatekData()->format('Y-m-d'));
    }
    public function testOnPreSubmit_nieAktualizujeDatJesliUwzgledniaDatyWwyszukiwaniu()
    {
        $formularz = [];
        $formularz['poczatekData']['day'] = 4;
        $formularz['poczatekData']['month'] = 7;
        $formularz['poczatekData']['year'] = 2006;
        $formularz['koniecData']['day'] = 3;
        $formularz['koniecData']['month'] = 8;
        $formularz['koniecData']['year'] = 2016;
        $wd = new WyszukiwanieDokumentow;
        $wd->setCzyDatyDoWyszukiwania(true);
        $rf = $wd->onPreSubmit($formularz);

        $this->assertEquals('2006-7-4',$rf['poczatekData']['year']."-".$rf['poczatekData']['month']."-".$rf['poczatekData']['day']);
        $this->assertEquals('2016-8-3',$rf['koniecData']['year']."-".$rf['koniecData']['month']."-".$rf['koniecData']['day']);
    }
    public function testOnPreSubmit_aktualizujeDatyJesliNieUwzgledniaDatWwyszukiwaniu()
    {
        $formularz = [];
        $formularz['poczatekData']['day'] = 4;
        $formularz['poczatekData']['month'] = 7;
        $formularz['poczatekData']['year'] = 2006;
        $formularz['koniecData']['day'] = 3;
        $formularz['koniecData']['month'] = 8;
        $formularz['koniecData']['year'] = 2016;

        $p1 = new Pismo;
        $p1->setDataDokumentu(new \DateTime('2019-02-23'));
        $p2 = new Pismo;
        $p2->setDataDokumentu(new \DateTime('2017-04-13'));
        
        $wd = new WyszukiwanieDokumentow;
        $wd->setCzyDatyDoWyszukiwania(false);
        $wd->UstawWyszukaneDokumenty([$p1,$p2]);
        $rf = $wd->onPreSubmit($formularz);

        $this->assertEquals('2017-4-13',$rf['poczatekData']['year']."-".$rf['poczatekData']['month']."-".$rf['poczatekData']['day']);
    }
    public function testDomyslnieNieUwzgledniaDat()
    {
        $wd = new WyszukiwanieDokumentow;
        $this->assertFalse($wd->getCzyDatyDoWyszukiwania());
    }
    public function testUstawDatyWformularzuJesliSa_NieUstaloneDaty()
    {
        $formularz = [];
        $formularz['poczatekData']['day'] = 4;
        $formularz['poczatekData']['month'] = 7;
        $formularz['poczatekData']['year'] = 2006;
        $formularz['koniecData']['day'] = 3;
        $formularz['koniecData']['month'] = 8;
        $formularz['koniecData']['year'] = 2016;

        $wd = new WyszukiwanieDokumentow;
        $wd->UstawDatyWformularzuJesliSa($formularz);
        $this->assertEquals(4,$formularz['poczatekData']['day']);
        $this->assertEquals(7,$formularz['poczatekData']['month']);
        $this->assertEquals(2006,$formularz['poczatekData']['year']);
        $this->assertEquals(3,$formularz['koniecData']['day']);
        $this->assertEquals(8,$formularz['koniecData']['month']);
        $this->assertEquals(2016,$formularz['koniecData']['year']);
    }
    public function testUstawDatyWformularzuJesliSa_UstaloneDaty()
    {
        $formularz = [];
        $formularz['poczatekData']['day'] = 4;
        $formularz['poczatekData']['month'] = 7;
        $formularz['poczatekData']['year'] = 2006;
        $formularz['koniecData']['day'] = 3;
        $formularz['koniecData']['month'] = 8;
        $formularz['koniecData']['year'] = 2016;

        $wd = new WyszukiwanieDokumentow;
        $wd->setPoczatekData(new \DateTime('2013-01-23'));
        $wd->setKoniecData(new \DateTime('2014-02-13'));
        $wd->UstawDatyWformularzuJesliSa($formularz);
        $this->assertEquals(23,$formularz['poczatekData']['day']);
        $this->assertEquals(1,$formularz['poczatekData']['month']);
        $this->assertEquals(2013,$formularz['poczatekData']['year']);
        $this->assertEquals(13,$formularz['koniecData']['day']);
        $this->assertEquals(2,$formularz['koniecData']['month']);
        $this->assertEquals(2014,$formularz['koniecData']['year']);
    }
}
