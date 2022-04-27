<?php

namespace App\Tests;

use App\Entity\Folder;
use PHPUnit\Framework\TestCase;

class FolderTest extends TestCase
{

    public function testSciezkaTuJestem_folder_root(): void
    {
        $folder = new Folder;
        $folder->setSciezkaMoja("/");
        $tuJestem = $folder->SciezkaTuJestem();
        $this->assertEquals("/", $tuJestem[0]['folder']);
    }
    public function testSciezkaTuJestem_pierwszyPoziom(): void
    {
        $folder = new Folder;
        $folder->setSciezkaMoja("/sciezka");
        $tuJestem = $folder->SciezkaTuJestem();
        $this->assertEquals("/", $tuJestem[0]['folder']);
        $this->assertEquals("sciezka", $tuJestem[1]['folder']);
    }
    public function testSciezkaTuJestem_foldery1(): void
    {
        $folder = new Folder;
        $folder->setSciezkaMoja("/jakas/sciezkaa");
        $tuJestem = $folder->SciezkaTuJestem();
        $this->assertEquals("jakas/", $tuJestem[1]['folder']);
        $this->assertEquals("sciezkaa", $tuJestem[2]['folder']);
    }
    public function testSciezkaTuJestem_foldery2(): void
    {
        $folder = new Folder;
        $folder->setSciezkaMoja("/jakas/inna/sciezka");
        $tuJestem = $folder->SciezkaTuJestem();
        $this->assertEquals("jakas/", $tuJestem[1]['folder']);
        $this->assertEquals("inna/", $tuJestem[2]['folder']);
        $this->assertEquals("sciezka", $tuJestem[3]['folder']);
    }
    
    public function testSciezkaTuJestem_folderPlus(): void
    {
        $folder = new Folder;
        $folder->setSciezkaMoja("/jak+as");
        $tuJestem = $folder->SciezkaTuJestem();
        $this->assertEquals("jak+as", $tuJestem[1]['folder']);
    }
    public function testSciezkaTuJestem_sciezkaRoot(): void
    {
        $folder = new Folder;
        $folder->setSciezkaMoja("/");
        $tuJestem = $folder->SciezkaTuJestem();
        $this->assertEquals("+", $tuJestem[0]['sciezka']);
    }
    public function testSciezkaTuJestem_sciezka1(): void
    {
        $folder = new Folder;
        $folder->setSciezkaMoja("/jakas/inna/sciezka");
        $tuJestem = $folder->SciezkaTuJestem();
        $this->assertEquals("+jakas+inna", $tuJestem[2]['sciezka']);
    }
    public function testSciezkaTuJestem_sciezkaplus(): void
    {
        $folder = new Folder;
        $folder->setSciezkaMoja("/jakas/in+na/sciezka");
        $tuJestem = $folder->SciezkaTuJestem();
        $this->assertEquals("+jakas+in++na", $tuJestem[2]['sciezka']);
    }
    public function testSciezkaTuJestem_sciezkaplusNaKoncu(): void
    {
        $folder = new Folder;
        $folder->setSciezkaMoja("/jakas/inna/sciez+ka");
        $tuJestem = $folder->SciezkaTuJestem();
        $this->assertEquals("+jakas+inna+sciez++ka", $tuJestem[3]['sciezka']);
    }
    public function testSciezkaKonwertujZadresu()
    {
        $folder = new Folder;
        $folder->SciezkePobierzZadresuIkonwertuj("+jakas+inna+sciezka");
        $this->assertEquals("/jakas/inna/sciezka",$folder->getSciezkaMoja());

    }
    public function testSciezkaKonwertujZadresu_plus()
    {
        $folder = new Folder;
        $folder->SciezkePobierzZadresuIkonwertuj("+ja++kas+inna+sciezka");
        $this->assertEquals("/ja+kas/inna/sciezka",$folder->getSciezkaMoja());

    }
    public function testSzablonSciezkaTuJestemDlaFolderBezId()
    {
        $folder = new Folder;
        $this->assertEquals('folder/_sciezkaTuJestemNew.html.twig',$folder->getSzablonSciezkaTuJestem());
    }
    public function testSzablonSciezkaTuJestemDlaFolderZId()
    {
        $folder = new Folder;
        $folder->setId(3);
        $this->assertEquals('folder/_sciezkaTuJestemEdit.html.twig',$folder->getSzablonSciezkaTuJestem());
    }
}
