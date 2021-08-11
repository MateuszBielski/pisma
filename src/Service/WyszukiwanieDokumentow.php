<?php

namespace App\Service;

use App\Controller\PismoController;
use App\Entity\Pismo;
use App\Repository\KontrahentRepository;
use App\Repository\PismoRepository;
use App\Repository\SprawaRepository;
use Symfony\Component\Process\Process;

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

       $pisma = $pr->WyszukajPoFragmentachOpisuKontrahIsprawy(
        $this->dokument,$this->sprawa,$this->kontrahent);
        foreach($pisma as $p)
        {
            $p->UstalStroneIKierunek();
            // $p->setSciezkaDoFolderuPdf($foldPdf);
            $p->UstalJesliTrzebaDateDokumentuZdatyMod();
            $p->setSciezkaGenerUrl($pc->GenerujUrlPismoShow_IdStrona($p->getId(),1));
        }

        $sprawy = [];
        if (strlen($this->sprawa))
        $sprawy = $sr->wyszukajPoFragmentachWyrazuOpisu($this->sprawa);

        $kontrahenci = [];
        if (strlen($this->kontrahent))
        $kontrahenci = $kr->WyszukajPoFragmencieNazwy($this->kontrahent);
        $this->wyszukaneDokumenty = $pisma;
        $this->wyszukaneSprawy = $sprawy;
        $this->wyszukaniKontrahenci = $kontrahenci;
        $this->UstalZakresDatWyszukanychDokumentow($pisma);
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
       $this->WyszukajUzywajac($this->pismoRepository, $this->sprawaRepository, $this->kontrahentRepository, $this->pismoController);
   }
   public function UstalZakresDatWyszukanychDokumentow(array $odszukaneDokumenty)
   {
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
}

