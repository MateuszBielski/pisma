<?php

namespace App\Service\PismoPrzetwarzanie;

use App\Entity\Pismo;
use App\Repository\PismoRepository;
use App\Service\PracaNaPlikach;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PismoPrzetwarzanieNowe extends PismoPrzetwarzanie
{
    private Pismo $nowyDokument;
    private PismoRepository $pr;


    public function __construct(PracaNaPlikach $pnp, UrlGeneratorInterface $router, EntityManagerInterface $em, PismoRepository $pr = null)
    {
        parent::__construct($pnp, $router, $em);
        if (isset($pr))
            $this->pr = $pr;
    }

    public function PrzedFormularzem()
    {

        $polozenie = (strlen($this->polozenie)) ? $this->polozenie : $this->polozenieDomyslne;
        if (!strlen($polozenie)) throw new Exception('należy ustawić domyślne położenie plików');
        $this->nowyDokument = $this->pnp->UtworzPismoNaPodstawie('', $this->nazwaPliku);
        $ostatniePismo = isset($this->pr)? $this->pr->OstatniNumerPrzychodzacych() : new Pismo;
        if($ostatniePismo == null) $ostatniePismo = new Pismo;
        
        $this->nowyDokument->setOznaczenie($ostatniePismo->NaPodstawieMojegoOznZaproponujOznaczenieZaktualnymRokiem());
    }
    public function NowyDokument(): Pismo
    {
        return $this->nowyDokument;
    }
    // $przetwarzanie->setNazwaPliku('nazwaPliku.odt');
    //     $przetwarzanie->PrzedFormularzem();
    //     $this->assertEquals('nazwaPliku.odt',$przetwarzanie->NowyDokument()->nazwaPliku());
}
