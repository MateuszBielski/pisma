<?php

namespace App\Service;

use App\Controller\PismoController;
use App\Entity\Pismo;
use App\Repository\KontrahentRepository;
use App\Repository\PismoRepository;
use App\Repository\SprawaRepository;
use DateTimeInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Stopwatch\Stopwatch;

class WyszukiwanieDokumentow
{
   private $dokument = '';
   private $sprawa = '';
   private $kontrahent = '';
   private $poczatekData;
   private $koniecData;
   private $pismoRepository;
   private $sprawaRepository;
   private $kontrahentRepository;
   private $pismoController;
   private $ustawioneRepo = false;
   private $czyDatyDoWyszukiwania = false;
   private $stopwatch;
   private $wyszukaneDokumenty = [];

    public function UstawStopWatch(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }
   
   public function getDokument()
   {
       return $this->dokument;
   }
   public function setDokument($dokument)
   {
        if(null !== $dokument)   
        $this->dokument = $dokument;
        return $this;
   }
   public function getSprawa()
   {
       return $this->sprawa;
   }
   public function setSprawa($sprawa)
   {
        if(null !== $sprawa)
        $this->sprawa = $sprawa;
        return $this;
   }
   public function getKontrahent()
   {
       return $this->kontrahent;
   }
   public function setKontrahent($kontrahent)
   {
        if(null !== $kontrahent)
        $this->kontrahent = $kontrahent;
       return $this;
   }
   public function getPoczatekData(): ?\DateTimeInterface
   {
       return $this->poczatekData;
   }
   public function setPoczatekData(?\DateTimeInterface $poczatekData)
   {
        if(null !== $poczatekData)
        $this->poczatekData= $poczatekData;
       return $this;
   }
   public function getKoniecData(): ?\DateTimeInterface
   {
       return $this->koniecData;
   }
   public function setKoniecData(?\DateTimeInterface $koniecData)
   {
        if(null !== $koniecData)
        $this->koniecData= $koniecData;
       return $this;
   }
   public function getCzyDatyDoWyszukiwania(): bool
   {
       return $this->czyDatyDoWyszukiwania;
   }
   public function setCzyDatyDoWyszukiwania(bool $czy)
   {
        $this->czyDatyDoWyszukiwania= $czy;
       return $this;
   }

   public function WyszukaneDokumenty()
   {
        return $this->wyszukaneDokumenty;
   }
   public function WyszukaneSprawy()
   {
       return $this->wyszukaneSprawy;
   }
   public function WyszukaniKontrahenci()
   {
       return $this->wyszukaniKontrahenci;
   }
   public function WyszukajUzywajac(PismoRepository $pr, SprawaRepository $sr, KontrahentRepository $kr,PismoController $pc)
   {
    //    $this->pismoRepository = $pr;
    //    $this->sprawaRepository = $sr;
    //    $this->kontrahentRepository = $kr;
    //    $this->pismoController = $pc;
    $this->stopwatch->start('WyszukajUzywajac');

    
       $pisma = $pr->WyszukajPoFragmentachOpisuKontrahIsprawy(
        $this->dokument,$this->sprawa,$this->kontrahent,
        $this->poczatekDataDlaRepo(),$this->koniecDataDlaRepo());
       

        $sprawy = [];
        if (strlen($this->sprawa))
        $sprawy = $sr->wyszukajPoFragmentachWyrazuOpisu($this->sprawa);

        $kontrahenci = [];
        if (strlen($this->kontrahent))
        $kontrahenci = $kr->WyszukajPoFragmencieNazwy($this->kontrahent);
        foreach($pisma as $p)
        {
            
            $p->UstalStroneIKierunek();
            // $p->setSciezkaDoFolderuPdf($foldPdf);
            $p->UstalJesliTrzebaDateDokumentuZdatyMod();
            $p->setSciezkaGenerUrl($pc->GenerujUrlPismoShow_IdStrona($p->getId(),1));
            $this->stopwatch->lap('WyszukajUzywajac');
        }
        $this->wyszukaneDokumenty = $pisma;
        $this->wyszukaneSprawy = $sprawy;
        $this->wyszukaniKontrahenci = $kontrahenci;

        $this->stopwatch->stop('WyszukajUzywajac');

        // $this->UstalZakresDatWyszukanychDokumentow($pisma);
   }
   public function UstawRepo(PismoRepository $pr, SprawaRepository $sr, KontrahentRepository $kr,PismoController $pc)
   {
       $this->pismoRepository = $pr;
       $this->sprawaRepository = $sr;
       $this->kontrahentRepository = $kr;
       $this->pismoController = $pc;
       $this->ustawioneRepo = true;
   }
   public function WyszukajDokumenty()
   {
        if ($this->ustawioneRepo)
        $this->WyszukajUzywajac($this->pismoRepository, $this->sprawaRepository, $this->kontrahentRepository, $this->pismoController);
   }
   public function UstalZakresDatWyszukanychDokumentow(array $odszukaneDokumenty)
   {
        
        
        if (!count($odszukaneDokumenty))return;
        $daty = [];
        foreach($odszukaneDokumenty as $d)
        {
            $daty[] = $d->getDataDokumentu();
        }
        $this->poczatekData=min($daty);
        $this->koniecData=max($daty);
   }
   public function UstawioneRepo()
   {
       return $this->ustawioneRepo;
   }
   public function PobierzDatyZformularzaJesliSa(array $formularz)
   {
       if(array_key_exists('poczatekData',$formularz))
       {
           $y = $formularz['poczatekData']['year'];
           $m = $formularz['poczatekData']['month'];
           $d = $formularz['poczatekData']['day'];
            $this->poczatekData = new \DateTime("$y-$m-$d");
       }
       if(array_key_exists('koniecData',$formularz))
       {
           $y = $formularz['koniecData']['year'];
           $m = $formularz['koniecData']['month'];
           $d = $formularz['koniecData']['day'];
            $this->koniecData = new \DateTime("$y-$m-$d");
       }
   }
   public function poczatekDataDlaRepo(): string
   {
        return ($this->poczatekData == null)?'':$this->poczatekData->format('Y-m-d');
   }
   public function koniecDataDlaRepo(): string
   {
        return ($this->koniecData == null)?'':$this->koniecData->format('Y-m-d');
   }
   public function UstawDatyWformularzuJesliSa(array &$formularz)
   {
        if( $this->poczatekData instanceof DateTimeInterface)
        {
            $poczatek = array_map('intval',explode('-',$this->poczatekData->format('Y-m-d')));
            $formularz['poczatekData']['year'] = $poczatek[0];
            $formularz['poczatekData']['month'] = $poczatek[1];
            $formularz['poczatekData']['day'] = $poczatek[2];
        }
        if($this->koniecData instanceof DateTimeInterface)
        {
            $koniec = array_map('intval',explode('-',$this->koniecData->format('Y-m-d')));
            $formularz['koniecData']['year'] = $koniec[0];
            $formularz['koniecData']['month'] = $koniec[1];
            $formularz['koniecData']['day'] = $koniec[2];
        }
   }
   public function onPreSubmit(array $formularz): array
   {
        
        $this->dokument = @$formularz['dokument'];
        $this->sprawa = @$formularz['sprawa'];
        $this->kontrahent = @$formularz['kontrahent'];
        $this->PobierzDatyZformularzaJesliSa($formularz);
        $this->WyszukajDokumenty();
        $this->UstalZakresDatWyszukanychDokumentow($this->wyszukaneDokumenty());
        $this->UstawDatyWformularzuJesliSa($formularz);
        return $formularz;
   }
   public function UstawWyszukaneDokumenty(array $wyszDok)
   {
        $this->wyszukaneDokumenty = $wyszDok;
   }
}

