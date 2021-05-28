<?php

namespace App\Service;

use App\Entity\Pismo;

class PracaNaPlikach
{
    private $odczytaneWszystkieNazwy;
    private $folderOdczytu;
    protected $uruchomienie;

    public function PobierzWszystkieNazwyPlikowZfolderu(string $path): array
    {
        
        
        $nazwy = @array_diff(@scandir($path), array('..', '.'));
        if(!$nazwy)$nazwy = [] ;
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
    public function UtworzPismoNaPodstawie($folder,$nazwaZrodla): Pismo
    {
        if(substr($folder,-1) != '/')$folder .= '/';
        return new Pismo($folder.$nazwaZrodla);
    }
    public function UtworzPismaZfolderu(string $folder, $rozsz = ""): array
    {
        $pisma = [];
        $this->PobierzWszystkieNazwyPlikowZfolderu($folder);
        $nazwy = strlen($rozsz)? $this->NazwyBezSciezkiZrozszerzeniem($rozsz): $this->odczytaneWszystkieNazwy;
        foreach($nazwy as $n)
            $pisma[] = $this->UtworzPismoNaPodstawie($this->folderOdczytu,$n);
        return $pisma;
    }
    public function GenerujPodgladJesliNieMaDlaPisma(string $folderPng, Pismo $pismo)
    {
        $pathFolderDlaJednegoDokumentu =  $folderPng.$pismo->NazwaZrodlaBezRozszerzenia();
        
        if(!file_exists($pathFolderDlaJednegoDokumentu))
        {
            mkdir($pathFolderDlaJednegoDokumentu,0777,true);
            $zrodlo = $pismo->getAdresZrodlaPrzedZarejestrowaniem();
            $cel = $pismo->FolderZpodlgademPngWzglednieZgodnieZeZrodlem().$pismo->NazwaZrodlaBezRozszerzenia();
            if($this->uruchomienie)
            $this->uruchomienie->UruchomPolecenie(['pdftopng',$zrodlo,$cel]);
        }
        

    }
    public function PrzeniesPlikiPdfiPodgladu(string $sciezkaDoZarejestrowanych,Pismo $pismo): bool
    {
        $adresZrodla = $pismo->getAdresZrodlaPrzedZarejestrowaniem();
        $nazwaPliku = $pismo->getNazwaPliku();
        $jestPodglad = $pismo->JestPodgladDlaZrodla();
        $adresPlikuPoZarejestrowaniu = $sciezkaDoZarejestrowanych.$nazwaPliku;
        if(!file_exists($adresZrodla))return false;
        //$przeniesioneZrodlo = rename($adresZrodla,$adresPlikuPoZarejestrowaniu);//nie chce działać na dysku zamontowanym z Windowsa
        $przeniesioneZrodlo = false;
        if(copy($adresZrodla,$adresPlikuPoZarejestrowaniu))
        {
            unlink($adresZrodla);
            $przeniesioneZrodlo = true;
        }
        if($jestPodglad && $przeniesioneZrodlo)
        {
            $sciezkiZrodla = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem(false);
            $sciezkiPoRejestracji = $pismo->GenerujNazwyDocelowychPodgladowZeSciezkamiWfolderzeZrodlowym();
            $i = 0;
            foreach($sciezkiZrodla as $sz)
            {
                rename($sz,$sciezkiPoRejestracji[$i++]);
            }
            $folderPodgladuPrzedRejestracja = $pismo->FolderZpodlgademPngWzglednieZgodnieZeZrodlem();
            $folderPodgladuPoZarejestrowaniu = $pismo->FolderZpodlgademPngWzglednieZgodnieZnazwaPliku();
            $zmienionyFolder = rename($folderPodgladuPrzedRejestracja,$folderPodgladuPoZarejestrowaniu);

            return true;
        }
        return $przeniesioneZrodlo;
    }
    public function UaktualnijNazwyPlikowPodgladu(Pismo $pismo)
    {
        $staraNazwa = $pismo->getNazwaPlikuPrzedZmiana();
        $nowaNazwa = $pismo->getNazwaPliku();
        if($staraNazwa != $nowaNazwa)
        {
            $sciezkiZrodla = $pismo->SciezkiDoPlikuPodgladowDlaNazwyPrzedZmiana(false);
            $sciezkiPoZmianie = $pismo->GenerujNazwyDocelowychPodgladowZeSciezkamiWfolderzePrzedZmiana();
            $i = 0;
            foreach($sciezkiZrodla as $sz)
            {
                rename($sz,$sciezkiPoZmianie[$i++]);
            }
            $folderPodgladuPrzedZmiana = $pismo->FolderZpodlgademPngWzglednieZgodnieZnazwaPrzedZmiana();
            $folderPodgladuPoZmianie = $pismo->FolderZpodlgademPngWzglednieZgodnieZnazwaPliku();
            $zmienionyFolder = rename($folderPodgladuPrzedZmiana,$folderPodgladuPoZmianie);
        }
    }
    public function setUruchomienieProcesu(UruchomienieProcesu $uruchomienie)
    {
        $this->uruchomienie = $uruchomienie;
    }
}