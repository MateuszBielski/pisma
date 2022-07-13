<?php

namespace App\Service;

class SciezkeZakonczSlashem
{
    public function __construct(&$sciezka)
    {
        if (substr($sciezka, -1) != "/") $sciezka .= "/";
    }
}
