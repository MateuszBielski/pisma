<?php

namespace App\Service;

use App\Entity\Pismo;
use Symfony\Component\Process\Process;

class UruchomienieProcesu
{
    
   public function UruchomPolecenie(array $polecenie)
   {
        // $polecenieStr = "";
        // foreach($polecenie as $p)$polecenieStr .= $p." ";
        // echo "UruchomienieProcesu::UruchomPolecenie".$polecenieStr;
       $process = new Process($polecenie);//'-gray',
        $process->run();
   }
}

