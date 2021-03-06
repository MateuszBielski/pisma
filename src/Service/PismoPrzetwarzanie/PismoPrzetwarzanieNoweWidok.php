<?php

namespace App\Service\PismoPrzetwarzanie;

use App\Entity\Pismo;
use Exception;

class PismoPrzetwarzanieNoweWidok
{
    private PismoPrzetwarzanie $przetwarzanie;
    private Pismo $dokument;
    private array $sciezkiDlaStron;
    private array $sciezkiDoPodgladow;
    private array $sciezkiDoPodgladowBezFolderuGlownego;
    private static array $widokDlaTypowPlikow = ['pdf','odt'];
    private string $rozszerzenie = '';
    // private int $ileStron = 0;

    public function __construct(PismoPrzetwarzanieNowe $przetwarzanie)
    {
        $this->przetwarzanie = $przetwarzanie;
        $dok = $przetwarzanie->NowyDokument();
        $ileStron = $dok->IleStron();
        $this->sciezkiDoPodgladow = $dok->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem();
        $this->sciezkiDoPodgladowBezFolderuGlownego = $dok->SciezkiDoPlikuPodgladowPrzedZarejestrowaniemBezFolderuGlownego();
        // $ileStron = count($this->sciezkiDoPodgladow);
        $router = $this->przetwarzanie->Router();
        $nazwa = $dok->getNazwaPliku();
        $num = 0;
        for ($i = 0; $i < $ileStron; $i++) {
            $this->sciezkiDlaStron[] = $router->generate('nowy_dokument', ['nazwa' => $nazwa, 'numerStrony' => ++$num]);
        }
        $this->dokument = $dok;
    }
    public function getSciezkiDlaStron(): array
    {

        return $this->sciezkiDlaStron;
    }
    public function getSciezkiDoPodgladow(): array
    {
        return $this->sciezkiDoPodgladow;
    }
    public function SciezkaDoPodgladu(int $nrStrony): string
    {
        return $this->sciezkiDoPodgladow[$nrStrony - 1];
    }
    public function SciezkaDoPodgladowBezFolderuGlownego(int $nrStrony): string
    {
        return $this->sciezkiDoPodgladowBezFolderuGlownego[$nrStrony - 1];
    }
    public function getSzablonNowyWidok(): string
    {
        $nazwa = $this->dokument->getNazwaPliku();
        $pos = strrpos($nazwa, ".") + 1;
        $this->rozszerzenie = substr($nazwa, $pos);
        if (!$this->TworzeniePodgladuObslugiwaneDla($this->rozszerzenie))
            throw new Exception('Generowanie podgl??du dla plik??w .' . $this->rozszerzenie . ' nieobs??ugiwane');
        return $this->dokument->SzablonNowyWidok();
    }
    protected function TworzeniePodgladuObslugiwaneDla(string $rozsz): bool
    {
        return in_array($rozsz, PismoPrzetwarzanieNoweWidok::$widokDlaTypowPlikow);;
    }
    public function UzupelnijDaneDlaGenerowaniaSzablonu(array &$parametry)
    {
        if (!array_key_exists('numerStrony', $parametry))
            throw new Exception('w parametrach do uzupe??nienia brak numeru strony');
        // $nrStrony = $parametry['numerStrony'];
        $parametry['pismo'] = $this->dokument;
        $parametry['sciezki_dla_stron'] = $this->getSciezkiDlaStron();
        $this->dokument->UzupelnijDaneDlaGenerowaniaSzablonuNoweWidok($parametry);// tu potrzebny numer strony
        // $parametry['sciezka_png'] = $this->SciezkaDoPodgladu($nrStrony);
        // $parametry['sciezka_png_bez_fg'] = $this->SciezkaDoPodgladowBezFolderuGlownego($nrStrony);
    }
}
