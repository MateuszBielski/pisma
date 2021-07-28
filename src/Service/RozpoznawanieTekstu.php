<?php

namespace App\Service;

use Imagick;
use thiagoalessio\TesseractOCR\TesseractOCR;

class RozpoznawanieTekstu
{
    private $folderNaWydzieloneFragmenty;
    public function WydzielFragment(string $orig,string $nowy, array $wydzielenie)
    {
        if(($wydzielenie['x1']-$wydzielenie['x0']) <= 0 ||
        ($wydzielenie['y1']-$wydzielenie['y0']) <= 0 
        )return;
        if(extension_loaded('gd')){
            $im = imagecreatefrompng($orig);
            $im2 = imagecrop($im,['width'=>$wydzielenie['x1'] - $wydzielenie['x0'],
                                    'height'=> $wydzielenie['y1'] - $wydzielenie['y0'],
                                    'x'=> $wydzielenie['x0'],
                                    'y'=> $wydzielenie['y0']
                                    ]);
            if ($im2 !== FALSE) {
            imagepng($im2, $nowy);
            imagedestroy($im2);
            }
            imagedestroy($im);
        }
        else if(class_exists(\Imagick::class)){

            $imagick = new \Imagick($orig);
            $imagick->cropImage($wydzielenie['x1'] - $wydzielenie['x0'], $wydzielenie['y1'] - $wydzielenie['y0'], $wydzielenie['x0'], $wydzielenie['y0']);
            $imagick->writeImage($nowy);
        }
    }
    public function RozpoznajTekstZpng(string $sciezkaPng)
    {
        if(!file_exists($sciezkaPng))return 'nie rozpoznano bo brak pliku fragmentu obrazu';
        $tesseract = new TesseractOCR($sciezkaPng);
        return $tesseract->lang('pol')->run();
    }
    public function FolderDlaWydzielonychFragmentow(?string $sciezka)
    {
        $this->folderNaWydzieloneFragmenty = $sciezka;
    }
    public function RozpoznajObrazPoWspolrzUlamkowych($polozenieObrazu,$fragmentWyrazonyUlamkami)
    {
        if(!$this->folderNaWydzieloneFragmenty || $this->folderNaWydzieloneFragmenty== '')
        return 'brak folderu na fragmenty obrazu';
        if(!file_exists($polozenieObrazu))
        return 'brak pliku obrazu: '.$polozenieObrazu;
        $w = getimagesize($polozenieObrazu);
        $szer = $w[0];
        $wys = $w[1];
        $fragmentWpikselach = [];
        $fragmentWpikselach['x0'] = round($fragmentWyrazonyUlamkami['xl']*$szer,0);
        $fragmentWpikselach['x1'] = round($fragmentWyrazonyUlamkami['xp']*$szer,0);
        $fragmentWpikselach['y0'] = round($fragmentWyrazonyUlamkami['yg']*$wys,0);
        $fragmentWpikselach['y1'] = round($fragmentWyrazonyUlamkami['yd']*$wys,0);
        
        $tymczasowyFragment = $this->folderNaWydzieloneFragmenty.
        $fragmentWpikselach['x0']."_".
        $fragmentWpikselach['x1']."_".
        $fragmentWpikselach['y0']."_".
        $fragmentWpikselach['y1']."_".
        uniqid().".png";
        $this->WydzielFragment($polozenieObrazu,$tymczasowyFragment,$fragmentWpikselach);
        $res = $this->RozpoznajTekstZpng($tymczasowyFragment);
        @unlink($tymczasowyFragment);
        return $res;
    }
}