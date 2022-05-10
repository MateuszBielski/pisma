<?php

namespace App\Service\PismoPrzetwarzanie;

use App\Entity\Pismo;
use App\Service\PracaNaPlikach;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PismoPrzetwarzanieNowe extends PismoPrzetwarzanie
{
    private Pismo $nowyDokument;


    public function __construct(PracaNaPlikach $pnp, UrlGeneratorInterface $router, EntityManagerInterface $em)
    {
        parent::__construct($pnp, $router, $em);
    }

    public function PrzedFormularzem()
    {

        $polozenie = (strlen($this->polozenie)) ? $this->polozenie : $this->polozenieDomyslne;
        if (!strlen($polozenie)) throw new Exception('należy ustawić domyślne położenie plików');
        $this->nowyDokument = $this->pnp->UtworzPismoNaPodstawie('', $this->nazwaPliku);
    }
    public function NowyDokument(): Pismo
    {
        return $this->nowyDokument;
    }
    // $przetwarzanie->setNazwaPliku('nazwaPliku.odt');
    //     $przetwarzanie->PrzedFormularzem();
    //     $this->assertEquals('nazwaPliku.odt',$przetwarzanie->NowyDokument()->nazwaPliku());
}
