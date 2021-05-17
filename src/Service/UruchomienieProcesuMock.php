<?php

namespace App\Service;

use App\Service\UruchomienieProcesu;

class UruchomienieProcesuMock  extends UruchomienieProcesu
{
    public $wywolanoProces = false;
    public $argumentyPolecenia = [];

    public function UruchomPolecenie(array $polecenie)
    {
        $this->wywolanoProces = true;
        $this->argumentyPolecenia = $polecenie;
    }
    
}
