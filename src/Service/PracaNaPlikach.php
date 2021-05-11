<?php

namespace App\Service;

class PracaNaPlikach
{
    private $odczytaneWszystkieNazwy;
    private $folderOdczytu;
    public function PobierzWszystkieNazwyPlikowZfolderu(string $path): array
    {
        
        
        $nazwy = array_diff(scandir($path), array('..', '.'));
        if(!count($nazwy)){
            $nazwy[] = "folder $path jest pusty";
            return $nazwy;
        }
        foreach($nazwy as &$n)$n = $path."/".$n;
        $this->odczytaneWszystkieNazwy = $nazwy;
        $this->folderOdczytu = $path;
        return $nazwy;
    }
    private function WydzielPlikiZrozszerzeniem(array $nazwy,string $rozsz): array
    {
        $nazwyFiltr = [];
        foreach($nazwy as $n)
        {
            $arr = explode('.',$n);
            $extension = end($arr);
            if($rozsz == $extension)$nazwyFiltr[] = $n;
        }
        return $nazwyFiltr;
    }
    private function ObetnijSciezke(array $nazwy): array
    {
        $result = [];
        foreach($nazwy as $n)
        {
            $arr = explode('/',$n);
            $result[] = end($arr);
        }
        return $result;
    }
    public function NazwyZrozszerzeniem(string $rozsz): array
    {
        $nazwyFiltr = $this->WydzielPlikiZrozszerzeniem($this->odczytaneWszystkieNazwy,$rozsz);
        if(!count($nazwyFiltr))$nazwyFiltr[] = "folder $this->folderOdczytu nie zawiera plików .".$rozsz;
        return $nazwyFiltr;
    }
    public function NazwyBezSciezkiZrozszerzeniem(string $rozsz): array
    {
        $nazwy = $this->ObetnijSciezke($this->WydzielPlikiZrozszerzeniem($this->odczytaneWszystkieNazwy,$rozsz));
        if(!count($nazwy))$nazwy[] = "folder $this->folderOdczytu nie zawiera plików .".$rozsz;
        return $nazwy;
    }
}