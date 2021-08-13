<?php

namespace App\Form;

use App\Service\PrzechwytywanieZselect2;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class WyszDokEventSubsc implements EventSubscriberInterface
{
    private $wyszukiwanie;
    private $formularz;
    
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::POST_SET_DATA => 'onPostSetData',
            FormEvents::PRE_SUBMIT   => 'onPreSubmit',
            FormEvents::SUBMIT => 'onSubmit',
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
        
        
    }

    public function onPostSetData(FormEvent $event)
    {
        //dane odczytane z obiektu, ustawione w formularzu
        //nowe, wpisane w formularz dane nie są jeszcze dostępne
        $this->wyszukiwanie = $event->getData();
        
       
    }

    public function onPreSubmit(FormEvent $event): void
    {
        //nowe dane z formularza są już dostępne, nie ma dostępu do aktulanego obiektu
        if(!$this->wyszukiwanie->UstawioneRepo())return;

        $this->formularz = $event->getData();
        $this->wyszukiwanie->setDokument($this->formularz['dokument']);
        $this->wyszukiwanie->setSprawa($this->formularz['sprawa']);
        $this->wyszukiwanie->setKontrahent($this->formularz['kontrahent']);
        $this->wyszukiwanie->PobierzDatyZformularzaJesliSa($this->formularz);
        $this->wyszukiwanie->WyszukajDokumenty();
        $this->wyszukiwanie->UstalZakresDatWyszukanychDokumentow($this->wyszukiwanie->WyszukaneDokumenty());
        
        $poczatek = array_map('intval',explode('-',$this->wyszukiwanie->getPoczatekData()->format('Y-m-d')));
        $this->formularz['poczatekData']['year'] = $poczatek[0];
        $this->formularz['poczatekData']['month'] = $poczatek[1];
        $this->formularz['poczatekData']['day'] = $poczatek[2];
        $koniec = array_map('intval',explode('-',$this->wyszukiwanie->getKoniecData()->format('Y-m-d')));
        $this->formularz['koniecData']['year'] = $koniec[0];
        $this->formularz['koniecData']['month'] = $koniec[1];
        $this->formularz['koniecData']['day'] = $koniec[2];
        $event->setData($this->formularz);

    }
    public function onSubmit(FormEvent $event)
    {
        //w tym miejscu dane są już ustawione w obiekcie
        // echo "onSubmit";

        
    }
    public function onPostSubmit(FormEvent $event)
    {
        

        
    }

}