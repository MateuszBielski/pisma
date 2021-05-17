<?php

namespace App\Service;

use App\Entity\Pismo;
use Symfony\Component\Process\Process;

class UruchomienieProcesu
{
    
   public function UruchomPolecenie(array $polecenie)
   {
       echo "UruchomienieProcesu::UruchomPolecenie".$polecenie[0];
       
   }
}

