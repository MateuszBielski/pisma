<?php

namespace App\Service;

use App\Entity\DokumentOdt;

class DokumentOdtMock extends DokumentOdt
{
    public function setTrescDoZapisu(string $tresc)
    {
        $this->tresc = $tresc;
    }
}