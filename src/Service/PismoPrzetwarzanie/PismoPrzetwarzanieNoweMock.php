<?php 

namespace App\Service\PismoPrzetwarzanie;

use App\Entity\Pismo;

class PismoPrzetwarzanieNoweMock extends PismoPrzetwarzanieNowe
{
    public function __construct(PismoPrzetwarzanieArgumentyInterface $arg)
    {
        parent::__construct($arg);
        // $this->argumenty = $arg->Argumenty();
    }
    public function setDokument(Pismo $dok)
    {
        $this->nowyDokument = $dok; 
    }
}