<?php

namespace App\Tests;

use App\Service\RozpoznawanieTekstu;
use PHPUnit\Framework\TestCase;

class RozpoznawanieTekstuTest extends TestCase
{
    private $folderPng = "tests/rozpoznawanie/";
    public function testWydzielZadanyObszarGrafiki(): void
    {
        $sciezkaObrazOryg = $this->folderPng."oryg.png";
        $rt = new RozpoznawanieTekstu;
        $sciezkaObrazWydzielony = $this->folderPng."wydzielony.png";
        $wyciecie = [
            'x0' => 41,
            'y0' => 98,
            'x1' => 227,
            'y1' => 119,
        ];
        $rt->WydzielFragment($sciezkaObrazOryg,$sciezkaObrazWydzielony,$wyciecie);
        $this->assertTrue(file_exists($sciezkaObrazWydzielony));
        $wymiary = getimagesize($sciezkaObrazWydzielony);
        $this->assertEquals(227-41,$wymiary[0]);
        $this->assertEquals(119-98,$wymiary[1]);
        @unlink($sciezkaObrazWydzielony);
    }
    public function testRozpoznajObrazPoWspolrzUlamkowych_komunikatJesliNiePodanyFolderNaFragmenty()
    {
        $rt = new RozpoznawanieTekstu;
        $rt->FolderDlaWydzielonychFragmentow(null);
        $this->assertEquals('brak folderu na fragmenty obrazu',$rt->RozpoznajObrazPoWspolrzUlamkowych('',[]));
    }
    public function testRozpoznajObrazPoWspolrzUlamkowych_komunikatJesliBrakObrazu()
    {
        $rt = new RozpoznawanieTekstu;
        $rt->FolderDlaWydzielonychFragmentow('jakiś_folder');
        $this->assertEquals('brak pliku obrazu: '.$this->folderPng."obrazJakiś.png",
        $rt->RozpoznajObrazPoWspolrzUlamkowych($this->folderPng."obrazJakiś.png",[]));
    }
    public function testRozpoznajObrazPoWspolrzUlamkowych_niePozostawiaObrazuTymczasowego()
    {
        $fragmentWyrazonyUlamkami = [
            'x0' => 0.1,
            'y0' => 0.2,
            'x1' => 0.3,
            'y1' => 0.4,
        ];
        $polozenieObrazu = $this->folderPng."oryg.png";
        $foderNaFragmenty = $this->folderPng;
        $rt = new RozpoznawanieTekstu;
        $rt->FolderDlaWydzielonychFragmentow($foderNaFragmenty);
        $rt->RozpoznajObrazPoWspolrzUlamkowych($polozenieObrazu,$fragmentWyrazonyUlamkami);
        $plikiPozostaleWfolderze = @array_diff(@scandir($foderNaFragmenty), array('..', '.'));
        $this->assertEquals(2,count($plikiPozostaleWfolderze));
    }
    public function testRozpoznajObrazPoWspolrzUlamkowych_rozpoznanie()
    {
        $fragmentWyrazonyUlamkami = [
            'x0' => 41/640,
            'y0' => 98/400,
            'x1' => 227/640,
            'y1' => 119/400,
        ];
        $polozenieObrazu = $this->folderPng."oryg.png";;
        $rt = new RozpoznawanieTekstu;
        $rt->FolderDlaWydzielonychFragmentow($this->folderPng);
        $this->assertEquals('tekst do rozpoznania',$rt->RozpoznajObrazPoWspolrzUlamkowych($polozenieObrazu,$fragmentWyrazonyUlamkami));
    }
}
