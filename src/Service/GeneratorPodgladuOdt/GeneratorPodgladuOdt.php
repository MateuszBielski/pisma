<?php

namespace App\Service\GeneratorPodgladuOdt;

use App\Entity\DokumentOdt;
use App\Service\SciezkeZakonczSlashem;
use Exception;

class GeneratorPodgladuOdt
{
    private string $folderPodgladuDlaOdt = '';
    private ?DokumentOdt $dokument = null;
    private string $rozszPodgl = '.html';
    private string $folderPodgladuCalaSciezka;
    public function Wykonaj()
    {
        if (!isset($this->folderPodgladuDlaOdt) || !strlen($this->folderPodgladuDlaOdt))
            throw new Exception('Należy ustawić folder dla podglądu Odt');
        if ($this->dokument == null)
            throw new Exception('nie ustawiony dokument, nie można wykonać podglądu');
        $nazwaPlBezRoz = $this->dokument->NazwaZrodlaBezRozszerzenia();
        $num = 1;
        $nazwaPlikuZeSciezka = $this->UtworzAdresPodgladuStrony($this->folderPodgladuDlaOdt, $nazwaPlBezRoz, $num);
        $this->dokument->setSciezkaZnazwaPlikuPodgladuAktualnejStrony($nazwaPlikuZeSciezka);
        if (file_exists($nazwaPlikuZeSciezka)) return;
        if (!is_dir($this->folderPodgladuCalaSciezka))    mkdir($this->folderPodgladuCalaSciezka, 0777, true);

        $tresc = $this->TrescDoZapisu();
        $this->ZapiszDoPlikuTresc($nazwaPlikuZeSciezka, $tresc);
    }
    public function setParametry(array $par)
    {
        $this->folderPodgladuDlaOdt = $par['folderPodgladuOdt'] ?? '';
        $this->dokument = $par['podgladDla'] ?? null; //new DokumentOdt();
    }
    public function ZapiszDoPlikuTresc(string $nazwaPlikuZeSciezka, string $tresc)
    {
        if (!strlen($tresc)) return;
        $plikPodgladu = fopen($nazwaPlikuZeSciezka, 'w');
        fwrite($plikPodgladu, $tresc);
        fclose($plikPodgladu);
    }
    public function TrescDoZapisu(): string
    {
        return $this->dokument->Tresc();
    }
    public function UtworzAdresPodgladuStrony($folderPodgladu, $nazwaPlBezRoz, $num)
    {
        $folderWewnetrzny = $nazwaPlBezRoz;
        new SciezkeZakonczSlashem($folderPodgladu);
        $folderPodgladuCalaSciezka = $folderPodgladu . $folderWewnetrzny;
        new SciezkeZakonczSlashem($folderPodgladuCalaSciezka);
        $nazwaPlikuPodgladu = $nazwaPlBezRoz . "-" . sprintf('%04s', $num) . $this->rozszPodgl;
        $this->folderPodgladuCalaSciezka = $folderPodgladuCalaSciezka;
        return $folderPodgladuCalaSciezka . $nazwaPlikuPodgladu;
    }
}
