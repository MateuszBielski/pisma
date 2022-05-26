<?php

namespace App\Service;

use App\Entity\Kontrahent;
use App\Entity\Pismo;
use App\Entity\RodzajDokumentu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

// use App\Entity\Pismo;
// use Symfony\Component\Process\Process;

class PrzechwytywanieZselect2
{
    private $utworzycNowegoKontrahenta = false;
    private $utworzycNowyRodzaj = false;
    private $opisySpraw = [];
    private $nowaNazwaRodzaju = '';
    private $nowaNazwaKontrahenta = '';

    public function przechwyc(Request $request, array $pola)
    {
        foreach ($pola as $n => $v) {
            $obiekt = [];
            $obiekt[$v] = null;
            $odczytanaWartosc = $request->request->get($n)[$v];
            if (!is_numeric($odczytanaWartosc) && strlen($odczytanaWartosc))
                $request->request->set($n, $obiekt);
        }
        // ['nObiekt' => 'nPole']
    }
    public function przechwycNazweStronyDlaPisma(Request $request)
    {
        $pismo = $request->request->get('pismo');
        // $strona = $pismo['strona'] ?? '';
        // if (!is_numeric($strona) && strlen($strona)) {
        //     $this->utworzycNowegoKontrahenta = true;
        //     $this->nowaNazwaKontrahenta = $strona;
        //     $pismo['strona'] = null;
        // }
        $this->PrzechwycNazweStronyIkierunekZtablicy($pismo);
        $request->request->set('pismo', $pismo);
    }
    public function PrzechwycNazweStronyIkierunekZtablicy(array &$form)
    {
        $strona = $form['strona'] ?? '';
        if (!is_numeric($strona) && strlen($strona)) {
            $this->utworzycNowegoKontrahenta = true;
            $this->nowaNazwaKontrahenta = $strona;
            $form['strona'] = null;
            // $request->request->set('pismo', $pismo);
        }
        $this->kierunek = $form['kierunek'] ?? '1';
    }
    public function przechwycRodzajDokumentuDlaPisma(Request $request)
    {
        $pismo = $request->request->get('pismo');
        // $rodzaj = $pismo['rodzaj']??'';
        // if(!is_numeric($rodzaj) && strlen($rodzaj))
        // {
        //     $this->utworzycNowyRodzaj = true;
        //     $this->nowaNazwaRodzaju = $rodzaj;
        //     $pismo['rodzaj'] = null;
        // }
        $this->przechwycRodzajDokumentuZtablicy($pismo);
        $request->request->set('pismo', $pismo);
    }
    public function przechwycRodzajDokumentuZtablicy(array &$form)
    {
        $rodzaj = $form['rodzaj'] ?? '';
        if (!is_numeric($rodzaj) && strlen($rodzaj)) {
            $this->utworzycNowyRodzaj = true;
            $this->nowaNazwaRodzaju = $rodzaj;
            $form['rodzaj'] = null;
            // $request->request->set('pismo',$pismo);
        }
    }
    // public function PrzechwycRodzajDokumentu(array $formularz)
    // {
    //     # code...
    // }
    public function PrzechwyconyRodzajDokumentu(): string
    {
        return $this->nowaNazwaRodzaju;
    }
    public function PrzechwyconyKierunek()
    {
        return $this->kierunek;
    }
    public function PrzechwyconaNazwaStrony()
    {
        return $this->nowaNazwaKontrahenta;
    }
    public function przechwyconaNazweStronyDlaPismaUtrwal(Pismo $pismo, EntityManagerInterface $em)
    {
        if ($this->utworzycNowegoKontrahenta) {
            $nowyKontrahent = new Kontrahent;
            $nowyKontrahent->setNazwa($this->nowaNazwaKontrahenta);

            $em->persist($nowyKontrahent);
            $pismo->setStrona($nowyKontrahent);
        }
    }
    public function przechwyconyRodzajDokumentuDlaPismaUtrwal(Pismo $pismo, EntityManagerInterface $em)
    {
        if ($this->utworzycNowyRodzaj) {
            $nowyRodzaj = new RodzajDokumentu;
            $nowyRodzaj->setNazwa($this->nowaNazwaRodzaju);
            $em->persist($nowyRodzaj);
            $pismo->setRodzaj($nowyRodzaj);
        }
    }
    public function PrzechwycOpisyNowychSprawZostawiajacZapisane(array &$sprawy)
    {
        $doUsuniecia = [];
        $this->opisySpraw = [];
        foreach ($sprawy as $s) {
            if (!is_numeric($s)) {
                // $sprawa = new Sprawa;
                // $sprawa->setOpis($s);
                // $this->addSprawy(($sprawa));
                $this->opisySpraw[] = $s;
                $doUsuniecia[] = $s;
            }
        }
        $sprawy = array_values(array_diff($sprawy, $doUsuniecia));
    }
    public function PrzechwyconeOpisySpraw(): array
    {
        return $this->opisySpraw;
    }
}
