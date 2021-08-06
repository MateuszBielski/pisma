<?php

namespace App\Service;

use App\Entity\Pismo;
use Symfony\Component\Process\Process;

class WyszukiwanieDokumentow
{
   private $dokument;
   private $sprawa;
   private $kontrahent;
   
   public function getDokument()
   {
       return $this->dokument;
   }
   public function setDokument($dokument)
   {
       $this->dokument = $dokument;
       return $this;
   }
   public function getSprawa()
   {
       return $this->sprawa;
   }
   public function setSprawa($sprawa)
   {
       $this->sprawa = $sprawa;
       return $this;
   }
   public function getKontrahent()
   {
       return $this->kontrahent;
   }
   public function setKontrahent($kontrahent)
   {
       $this->kontrahent;
       return $this;
   }
}

