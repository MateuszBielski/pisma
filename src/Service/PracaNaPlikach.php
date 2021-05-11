<?php

namespace App\Service;

class PracaNaPlikach
{
    private $odczytaneWszystkieNazwy;
    public function PobierzWszystkieNazwyPlikowZfolderu(string $path): array
    {
        
        $nazwy = array_diff(scandir($path), array('..', '.'));
        foreach($nazwy as &$n)$n = $path."/".$n;
        $this->odczytaneWszystkieNazwy = $nazwy;
        return $nazwy;
    }
    public function NazwyZrozszerzeniem(string $rozsz): array
    {
        $nazwyFiltr = [];
        foreach($this->odczytaneWszystkieNazwy as $n)
        {
            $arr = explode('.',$n);
            $extension = end($arr);
            if($rozsz == $extension)$nazwyFiltr[] = $n;
        }
        return $nazwyFiltr;
    }
}