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
}
