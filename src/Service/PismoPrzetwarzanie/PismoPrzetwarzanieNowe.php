<?php

namespace App\Service\PismoPrzetwarzanie;

use App\Entity\Pismo;
use App\Repository\PismoRepository;
use App\Service\PracaNaPlikach;
use App\Service\UruchomienieProcesu;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PismoPrzetwarzanieNowe extends PismoPrzetwarzanie
{
    private Pismo $nowyDokument;
    private PismoRepository $pr;
    private static array $podgladDlaTypowPlikow = ['odt','pdf'];


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
        $this->pnp->setUruchomienieProcesu(new UruchomienieProcesu);
        $rozsz = $this->pnp->RozszerzeniePliku();
        if (!$this->TworzeniePodgladuObslugiwaneDla($rozsz)) throw new Exception('Generowanie podglądu dla plików .'.$rozsz.' nieobsługiwane');
        $this->pnp->GenerujPodgladJesliNieMaDlaPisma('trzeba zapewnic prawidlowa sciezke dla podgladu', $this->nowyDokument);
    }
    public function NowyDokument(): Pismo
    {
        return $this->nowyDokument;
    }
    public function TworzeniePodgladuObslugiwaneDla(string $rozsz)
    {
        return in_array($rozsz,PismoPrzetwarzanieNowe::$podgladDlaTypowPlikow);
    }
    
}
