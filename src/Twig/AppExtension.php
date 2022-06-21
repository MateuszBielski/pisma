<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('zawartoscPliku', [$this, 'wypiszZawartoscPliku']),
        ];
    }

    public function wypiszZawartoscPliku(string $adresPliku): string
    {
        //jesteśmy w katalogu pisma/src/Twig/
        $l = filesize($adresPliku);
        $f = fopen($adresPliku,'r');
        $tresc = fread($f,$l);
        fclose($f);
        return $tresc;
        // return $width * $length;
    }
}