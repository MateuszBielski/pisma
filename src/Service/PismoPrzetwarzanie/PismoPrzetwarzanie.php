<?php

namespace App\Service\PismoPrzetwarzanie;

use App\Entity\Pismo;
use App\Service\PracaNaPlikach;
use App\Service\SciezkaKodowanieZnakow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Stopwatch\Stopwatch;

abstract class PismoPrzetwarzanie
{
    protected EntityManagerInterface $em;
    protected PracaNaPlikach $pnp;
    protected UrlGeneratorInterface $router;
    private bool $zainicjowane = false;
    // protected Pismo $pismo;
    protected string $sciezkaLubNazwaPliku = '';
    protected string $nazwaPliku = '';
    protected string $polozenieDomyslne = '';
    protected string $polozenie = '';
    protected string $docelowePolozeniePliku = '';
    protected bool $rezultatWalidacjiFormularza = false;
    protected bool $nieZnanyRezultatFormularza = true;
    protected Stopwatch $stopwatch;
    protected array $argumenty;
    // protected $startPomiar; //chciałem do tej zmiennej przypisać metodę klasy stopwatch, ale nie wiem jak
    // protected $stopPomiar;
    // protected string $rozszerzenie = '';

    private function PustaFunkcja()
    {

    }

    protected function __construct(PracaNaPlikach $pnp, UrlGeneratorInterface $router, EntityManagerInterface $em = null)
    {
        $this->pnp = $pnp;
        $this->router = $router;
        if (isset($em))
            $this->em = $em;
        // $this->startPomiar = $this->PustaFunkcja();
        // $this->stopPomiar = $this->PustaFunkcja();
    }
    ##########
    protected function StartPomiar(string $oznaczenie)
    {
        if (isset($this->stopwatch))
            $this->stopwatch->start($oznaczenie);
    }
    protected function StopPomiar(string $oznaczenie)
    {
        if (isset($this->stopwatch))
            $this->stopwatch->stop($oznaczenie);
    }
    ############
    public function Zainicjowane(): bool
    {
        $this->zainicjowane = true;
        if (!isset($this->em)) $this->zainicjowane = false;
        if (!isset($this->router)) $this->zainicjowane = false;
        if (!isset($this->pnp)) $this->zainicjowane = false;
        return $this->zainicjowane;
    }
    public function setSciezkaLubNazwaPliku(string $nazwa)
    {
        $kod = new SciezkaKodowanieZnakow;
        $nazwa = $kod->Dekoduj($nazwa);
        $pos = strrpos($nazwa, "/");
        $this->sciezkaLubNazwaPliku = $nazwa;
        $this->polozenie = substr($nazwa, 0, $pos);
        $this->nazwaPliku = substr($nazwa, $pos);
    }
    public function setDomyslnePolozeniePliku(string $polozenie)
    {
        $this->polozenieDomyslne = $polozenie;
    }
    public function setDocelowePolozeniePliku(string $polozenie)
    {
        if (substr($polozenie, -1) != '/') $polozenie .= '/';
        $this->docelowePolozeniePliku = $polozenie;
    }
    public function setStopWatch(Stopwatch $sw)
    {
        $this->stopwatch = $sw;
        // $this->startPomiar = array($sw,'start');
        // $this->stopPomiar = array($sw,'stop');
        // $this->stopPomiar = $sw->start;
        // $this->stopPomiar = $sw->stop;
    }
    public function setParametry(array $parametry)
    {
        foreach ($parametry as $nazwaParametru => $parametr) {
            $Ustaw = 'set' . $nazwaParametru;
            $this->$Ustaw($parametr);
        }
    }
    public function getPracaNaPlikach()
    {
        return $this->pnp;
    }
    public function RezultatWalidacjiFormularza(bool $isValid)
    {
        $this->nieZnanyRezultatFormularza = false;
        $this->rezultatWalidacjiFormularza = $isValid;
    }
    public function Router(): UrlGeneratorInterface
    {
        return $this->router;
    }
}
