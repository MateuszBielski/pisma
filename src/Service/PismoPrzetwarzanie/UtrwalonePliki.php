<?php

namespace App\Service\PismoPrzetwarzanie;

class UtrwalonePliki
{
    private bool $utrwalone = false;
    public function __construct(bool $utrwalone = false)
    {
        $this->utrwalone = $utrwalone;
    }

    public function czyUtrwalone(): bool
    {
        return $this->utrwalone;
    }
    //powinna tu być informacja o miejscach, gdzie zapisano pliki
}