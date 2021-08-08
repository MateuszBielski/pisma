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
   private $pismoRepository;
   private $sprawaRepository;
   private $kontrahentRepository;
   private $pismoController;
   
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
        $this->kontrahent;
       return $this;
   }
   public function WyszukajUzywajac(PismoRepository &$pr, SprawaRepository &$sr, KontrahentRepository &$kr,PismoController &$pc)
   {
    //    $this->pismoRepository = $pr;
       $this->sprawaRepository = $sr;
       $this->kontrahentRepository = $kr;
       $this->pismoController = $pc;

       $pisma = $pr->WyszukajPoFragmentachOpisuKontrahIsprawy(
        $this->dokument,$this->sprawa,$this->kontrahent);
        foreach($pisma as $p)
        {
            $p->UstalStroneIKierunek();
            // $p->setSciezkaDoFolderuPdf($foldPdf);
            $p->UstalJesliTrzebaDateDokumentuZdatyMod();
            $p->setSciezkaGenerUrl($this->pismoController->GenerujUrlPismoShow_IdStrona($p->getId(),1));
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
}

