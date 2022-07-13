<?php

namespace App\Service;

class SciezkePrzytnijZlewejWzglednieDo
{
    public function __construct(&$sciezka, $przyciecie)
    {
        $pos = strpos($sciezka, $przyciecie);
        if ($pos != false) {
            $str = substr($sciezka, $pos);
            $pos = strpos($str, "/");
            $str = substr($str, $pos + 1);
            $sciezka = $str;
        }
    }
}
