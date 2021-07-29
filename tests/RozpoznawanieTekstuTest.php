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
    public function testRozpoznajObrazPoWspolrzUlamkowych_komunikatJesliPodanaSciezkaNieProwadziDoPliku()
    {
        $rt = new RozpoznawanieTekstu;
        $foderNaFragmenty = $this->folderPng;
        $polozenieObrazu = $this->folderPng;//nie ma nazwy pliku, chociaż folder istnieje
        $rt->FolderDlaWydzielonychFragmentow($foderNaFragmenty);
        $this->assertEquals('brak pliku obrazu: '.$this->folderPng,
        $rt->RozpoznajObrazPoWspolrzUlamkowych($polozenieObrazu,[]));
    }
    public function testRozpoznajObrazPoWspolrzUlamkowych_niePozostawiaObrazuTymczasowego()
    {
        $fragmentWyrazonyUlamkami = [
            'xl' => 0.1,
            'yg' => 0.2,
            'xp' => 0.3,
            'yd' => 0.4,
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
            'xl' => 41/640,
            'yg' => 98/400,
            'xp' => 227/640,
            'yd' => 119/400,
        ];
        $polozenieObrazu = $this->folderPng."oryg.png";;
        $rt = new RozpoznawanieTekstu;
        $rt->FolderDlaWydzielonychFragmentow($this->folderPng);
        $this->assertEquals('tekst do rozpoznania',$rt->RozpoznajObrazPoWspolrzUlamkowych($polozenieObrazu,$fragmentWyrazonyUlamkami));
    }
    public function testWydzielFragment_nieWydzielaJesliNieprawidloweWartosci_Xzerowy()
    {
        $sciezkaObrazOryg = $this->folderPng."oryg.png";
        $rt = new RozpoznawanieTekstu;
        $sciezkaObrazWydzielony = $this->folderPng."wydzielony.png";
        $wyciecie = [
            'x0' => 41,
            'y0' => 98,
            'x1' => 41,
            'y1' => 119,
        ];
        $rt->WydzielFragment($sciezkaObrazOryg,$sciezkaObrazWydzielony,$wyciecie);
        $this->assertFalse(file_exists($sciezkaObrazWydzielony));
        @unlink($sciezkaObrazWydzielony);
    }
    public function testWydzielFragment_nieWydzielaJesliNieprawidloweWartosci_Yzerowy_Xujemny()
    {
        $sciezkaObrazOryg = $this->folderPng."oryg.png";
        $rt = new RozpoznawanieTekstu;
        $sciezkaObrazWydzielony = $this->folderPng."wydzielony.png";
        $wyciecie = [
            'x0' => 41,
            'y0' => 119,
            'x1' => 27,
            'y1' => 119,
        ];
        $rt->WydzielFragment($sciezkaObrazOryg,$sciezkaObrazWydzielony,$wyciecie);
        $this->assertFalse(file_exists($sciezkaObrazWydzielony));
        @unlink($sciezkaObrazWydzielony);
    }
    public function testRozpoznaj_KomunikatJesliBrakPlikuFragmentu()
    {
        $tr = new RozpoznawanieTekstu;
        $this->assertEquals('nie rozpoznano bo brak pliku fragmentu obrazu',
        $tr->RozpoznajTekstZpng('nieistniejacyPlik'));
    }
}
