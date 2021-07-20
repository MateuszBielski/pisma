<?php

namespace App\Service;

// use Imagick;
use thiagoalessio\TesseractOCR\TesseractOCR;

class RozpoznawanieTekstu
{
    public function WydzielFragment(string $orig,string $nowy, array $wydzielenie)
    {
        // $plikWydzielony = fopen($nowy,'w');
        // fclose($plikWydzielony );
        /*
        $im = imagecreatefrompng($orig);
        // $size = min(imagesx($im), imagesy($im));
        $im2 = imagecrop($im, ['x' => $wydzielenie['x0'], 'y' => $wydzielenie['y0'], 
            'width' => $wydzielenie['x1'] - $wydzielenie['x0'], 
            'height' => $wydzielenie['y1'] - $wydzielenie['y0']]);
        if ($im2 !== FALSE) {
            imagepng($im2, $nowy);
            imagedestroy($im2);
        }
        imagedestroy($im);
        */
        $imagick = new \Imagick($orig);
        $imagick->cropImage($wydzielenie['x1'] - $wydzielenie['x0'], $wydzielenie['y1'] - $wydzielenie['y0'], $wydzielenie['x0'], $wydzielenie['y0']);
        $imagick->writeImage($nowy);
    }
    public function RozpoznajTekstZpng(string $sciezkaPng)
    {
        $tesseract = new TesseractOCR($sciezkaPng);
        return $tesseract->run();
    }
}