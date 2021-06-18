<?php

namespace App\Service;

use App\Entity\Kontrahent;
use App\Entity\Pismo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

// use App\Entity\Pismo;
// use Symfony\Component\Process\Process;

class PrzechwytywanieZselect2
{
    private $utworzycNowegoKontrahenta = false;
    
    public function przechwyc(Request $request,array $pola)
    {
        foreach($pola as $n => $v){
            $obiekt = [];
            $obiekt[$v] = null;
            $odczytanaWartosc = $request->request->get($n)[$v];
            if(!is_numeric($odczytanaWartosc) && strlen($odczytanaWartosc))
            $request->request->set($n,$obiekt);
        }
        // ['nObiekt' => 'nPole']
    }
    public function przechwycNazweStronyDlaPisma(Request $request)
    {
        $pismo = $request->request->get('pismo');
        $strona = $pismo['strona'];
        if(!is_numeric($strona) && strlen($strona))
        {
            $this->utworzycNowegoKontrahenta = true;
            $this->nowaNazwaKontrahenta = $strona;
            $pismo['strona'] = null;
            $request->request->set('pismo',$pismo);
        }
    }
    public function przechwyconaNazweStronyDlaPismaUtrwal(Pismo $pismo, EntityManagerInterface $entityManager = null)
    {
        if($this->utworzycNowegoKontrahenta)
        {
            $nowyKontrahent = new Kontrahent;
            $nowyKontrahent->setNazwa($this->nowaNazwaKontrahenta);
            $entityManager->persist($nowyKontrahent);
            $pismo->setStrona($nowyKontrahent);
        }
    }
}