<?php

namespace App\Service;

use App\Service\PracaNaPlikach;
use App\Entity\Pismo;

class PracaNaPlikachMock extends PracaNaPlikach
{
private bool $wywolaneGenerujPodgladJesliNieMa = false;
    
    public function UruchomienieProcesuUstawione(): bool
    {
        return isset($this->uruchomienie);
    }
    public function WywolaneGenerujPodgladJesliNieMa()
    {
        return $this->wywolaneGenerujPodgladJesliNieMa;
    }
    public function GenerujPodgladJesliNieMaDlaPisma(string $folderPng, Pismo $pismo)
    {
        $this->wywolaneGenerujPodgladJesliNieMa = true;
    }
}
