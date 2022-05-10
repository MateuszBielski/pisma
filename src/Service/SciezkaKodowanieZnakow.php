<?php

namespace App\Service;

class SciezkaKodowanieZnakow
{
    public function Koduj(string $surowa): string
    {
        $res = str_replace("+", "++", $surowa);
        return str_replace("/", "+", $res);
    }
    public function Dekoduj(string $surowa): string
    {
        if (strpos($surowa,"/") !== false) return $surowa;
        $res = str_replace("+", "/", $surowa);
        return str_replace("//", "+", $res);
    }
}
