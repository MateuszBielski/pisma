<?php

namespace App\Tests;

use App\Entity\Pismo;
use App\Service\WyszukiwanieDokumentow;
use PHPUnit\Framework\TestCase;

class WyszukiwanieDokumentowTest extends TestCase
{
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
}
