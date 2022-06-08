<?php

namespace App\Service\GeneratorPodgladuOdt;

use App\Entity\DokumentOdt;
use Exception;

class GeneratorPodgladuOdt
{
    private string $folderPodgladuDlaOdt = '';
    private DokumentOdt $dokument;
    private string $rozszPodgl = '.html';
    public function Wykonaj()
    {
        if (!isset($this->folderPodgladuDlaOdt) || !strlen($this->folderPodgladuDlaOdt))
            throw new Exception('Należy ustawić folder dla podglądu Odt');
        $nazwaPlBezRoz = $this->dokument->NazwaZrodlaBezRozszerzenia();
        $folderWewnetrzny = $nazwaPlBezRoz;
        $folderPodgladuCalaSciezka = $this->folderPodgladuDlaOdt . $folderWewnetrzny;
        if (substr($folderPodgladuCalaSciezka, -1) != "/")
            $folderPodgladuCalaSciezka .= "/";
            
        $num = 1;
        $nazwaPlikuPodgladu = $nazwaPlBezRoz . "-" . sprintf('%04s', $num) . $this->rozszPodgl;
        $nazwaPlikuZeSciezka = $folderPodgladuCalaSciezka . $nazwaPlikuPodgladu;

        if(file_exists($nazwaPlikuZeSciezka)) return;
        if (!is_dir($folderPodgladuCalaSciezka))    mkdir($folderPodgladuCalaSciezka, 0777, true);

        $plikPodgladu = fopen($nazwaPlikuZeSciezka, 'w');
        fclose($plikPodgladu);
    }
    public function setParametry(array $par)
    {
        $this->folderPodgladuDlaOdt = $par['folderPodgladuOdt'] ?? '';
        $this->dokument = $par['podgladDla'] ?? new DokumentOdt();
    }
}
