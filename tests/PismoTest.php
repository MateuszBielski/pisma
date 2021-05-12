<?php

namespace App\Tests;

use App\Entity\Pismo;
use PHPUnit\Framework\TestCase;

class PismoTest extends TestCase
{
    public function testUtworzonePokazujePolozeniePodgladu1strony()
    {
        // $adrZrodla = "/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf";
        $pismo = new Pismo("/var/www/html/skany/BRN3C2AF41C02A8_006357.pdf");
        $this->assertTrue('/png/BRN3C2AF41C02A8_006357/BRN3C2AF41C02A8_006357.png',$pismo->PolozeniePierwszejStronyDuzegoPodgladu());
    }
}
