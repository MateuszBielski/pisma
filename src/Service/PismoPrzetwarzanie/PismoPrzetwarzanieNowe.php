<?php

namespace App\Service\PismoPrzetwarzanie;

use App\Entity\Pismo;
use App\Repository\PismoRepository;
use App\Service\GeneratorPodgladuOdt\GeneratorPodgladuOdt;
use App\Service\PracaNaPlikach;
use App\Service\UruchomienieProcesu;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PismoPrzetwarzanieNowe extends PismoPrzetwarzanie
{
    protected Pismo $nowyDokument;
    private PismoRepository $pr;
    private string $folderPodgladu = '';
    private string $folderPodgladuDlaOdt = '';
    private static array $podgladDlaTypowPlikow = ['pdf', 'odt']; //'odt' - musi być, chociaż to nieprawda, bo wiele testów pisanych było z założeniem, że jest podgląd
    private GeneratorPodgladuOdt $generatorPodgladuOdt;


    public function __construct(PismoPrzetwarzanieArgumentyInterface $argumenty)
    {
        $this->argumenty = $argumenty->Argumenty();
        if (array_key_exists('stopWatch', $this->argumenty)) $this->stopwatch = $this->argumenty['stopWatch'];
        if (array_key_exists('router', $this->argumenty)) $this->router = $this->argumenty['router'];
    }

    public function PrzedFormularzem()
    {
        $this->StartPomiar('PismoPrzetwarzanieNowe::PrzedFormularzem');
        $this->pnp = $this->argumenty['pnp'];
        if (array_key_exists('pismoRepository', $this->argumenty)) $this->pr = $this->argumenty['pismoRepository'];
        $polozenie = (strlen($this->polozenie)) ? $this->polozenie : $this->polozenieDomyslne;
        if (!strlen($polozenie)) throw new Exception('należy ustawić folder z dokumentami');
        $this->nowyDokument = $this->pnp->UtworzPismoNaPodstawie($polozenie, $this->nazwaPliku);
        $ostatniePismo = isset($this->pr) ? $this->pr->OstatniNumerPrzychodzacych() : new Pismo;
        if ($ostatniePismo == null) $ostatniePismo = new Pismo;

        $this->nowyDokument->setOznaczenie($ostatniePismo->NaPodstawieMojegoOznZaproponujOznaczenieZaktualnymRokiem());
        $this->pnp->setUruchomienieProcesu(new UruchomienieProcesu);
        $rozsz = $this->pnp->RozszerzeniePliku();
        if (!$this->TworzeniePodgladuObslugiwaneDla($rozsz)) throw new Exception('Generowanie podglądu dla plików .' . $rozsz . ' nieobsługiwane');
        $this->GenerujPodgladOdpowiedniDlaTypuPliku($rozsz);
        $this->StopPomiar('PismoPrzetwarzanieNowe::PrzedFormularzem');
    }
    public function UtrwalPliki(): UtrwalonePliki
    {
        $this->StartPomiar('PismoPrzetwarzanieNowe::UtrwalPliki');
        if ($this->nieZnanyRezultatFormularza) throw new Exception('Nie znany wynik walidacji formularza');
        if (strlen($this->docelowePolozeniePliku) && !is_dir($this->docelowePolozeniePliku)) return new UtrwalonePliki(false);
        if (!$this->rezultatWalidacjiFormularza) return new UtrwalonePliki(false);
        $polozeniePoZarejestrowaniu = $this->polozenie;
        if ($this->MoznaPrzenosicPlikDokumentu())
        {
            $this->pnp->PrzeniesPlikiPdfiPodgladu($this->docelowePolozeniePliku, $this->nowyDokument);
            $polozeniePoZarejestrowaniu = $this->docelowePolozeniePliku;
        }
        $this->ZapewnijUkosnikKonczacy($polozeniePoZarejestrowaniu);
        $this->nowyDokument->setPolozeniePoZarejestrowaniu($polozeniePoZarejestrowaniu);
        $result = new UtrwalonePliki(true);
        $this->StopPomiar('PismoPrzetwarzanieNowe::UtrwalPliki');
        return $result;
    }
    public function NowyDokument(): Pismo
    {
        return $this->nowyDokument;
    }
    public function setFolderDlaPlikowPodgladu(string $sciezka)
    {
        $this->folderPodgladu = $sciezka;
    }
    public function setFolderPodgladuDlaOdt(string $sciezka)
    {
        $this->folderPodgladuDlaOdt = $sciezka;
    }
    public function setGeneratorPodgladuOdtZamiastDomyslnego(GeneratorPodgladuOdt $generator)
    {
        $this->generatorPodgladuOdt = $generator;
    }
    protected function TworzeniePodgladuObslugiwaneDla(string $rozsz)
    {
        return in_array($rozsz, PismoPrzetwarzanieNowe::$podgladDlaTypowPlikow);
    }
    protected function GenerujPodgladOdpowiedniDlaTypuPliku(string $rozsz)
    {
        $funkcjePodgladuDlaTypowPliku = [
            'pdf' => 'PodgladPdf',
            'odt' => 'PodgladOdt',
        ];
        $PodgladFunkcja = $funkcjePodgladuDlaTypowPliku[$rozsz];
        $this->$PodgladFunkcja();
    }
    protected function PodgladPdf()
    {
        $this->pnp->GenerujPodgladJesliNieMaDlaPisma($this->folderPodgladu, $this->nowyDokument);
    }
    protected function PodgladOdt()
    {
        if (!isset($this->generatorPodgladuOdt))
            $this->generatorPodgladuOdt = new GeneratorPodgladuOdt();
        $this->generatorPodgladuOdt->setParametry([
            'folderPodgladuOdt' => $this->folderPodgladuDlaOdt,
            'podgladDla' => $this->nowyDokument,
        ]);
        $this->generatorPodgladuOdt->Wykonaj();
    }
    protected function MoznaPrzenosicPlikDokumentu(): bool
    {
        if(!strlen($this->polozenie)) return true;
        if($this->polozenie == $this->polozenieDomyslne) return true;
        return false;
    }
}
