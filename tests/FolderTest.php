<?php

namespace App\Tests;

use App\Entity\Folder;
use PHPUnit\Framework\TestCase;

class FolderTest extends TestCase
{

    public function testSciezkaTuJestem_foldery1(): void
    {
        $folder = new Folder;
        $folder->setSciezkaMoja("/jakas/sciezka");
        $tuJestem = $folder->SciezkaTuJestem();
        $this->assertEquals("jakas", $tuJestem[0]['folder']);
        $this->assertEquals("sciezka", $tuJestem[1]['folder']);
    }
    public function testSciezkaTuJestem_foldery2(): void
    {
        $folder = new Folder;
        $folder->setSciezkaMoja("/jakas/inna/sciezka");
        $tuJestem = $folder->SciezkaTuJestem();
        $this->assertEquals("jakas", $tuJestem[0]['folder']);
        $this->assertEquals("inna", $tuJestem[1]['folder']);
        $this->assertEquals("sciezka", $tuJestem[2]['folder']);
    }
    public function testSciezkaTuJestem_sciezka1(): void
    {
        $folder = new Folder;
        $folder->setSciezkaMoja("/jakas/inna/sciezka");
        $tuJestem = $folder->SciezkaTuJestem();
        $this->assertEquals("/jakas/inna", $tuJestem[1]['sciezka']);
    }
}
