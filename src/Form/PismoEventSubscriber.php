<?php

namespace App\Form;

use App\Service\PrzechwytywanieZselect2;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PismoEventSubscriber implements EventSubscriberInterface
{
    private $przechwytywanie;
    private $opisStr;
    private $staryOpisStr;
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
        //odczytuje zawsze w przed handle request
        $p = $event->getData();
        $this->staryOpisStr = $p->getOpis();
        // $oznaczenieZbazy = $p->getOznaczenie();
        $p->setOznaczenie($p->getOznaczenieUzytkownika());
        // $form = $event->getForm();
       
        
    }

    public function onPostSetData(FormEvent $event)
    {
        //dane odczytane z obiektu, ustawione w formularzu
        //nowe, wpisane w formularz dane nie są jeszcze dostępne
    }

    public function onPreSubmit(FormEvent $event): void
    {
        //nowe dane z formularza są już dostępne, nie ma dostępu do aktulanego obiektu
        $form = $event->getData();

        $this->przechwytywanie = new PrzechwytywanieZselect2;
        if(array_key_exists('sprawy',$form ))
        {
            $sprawy = $form['sprawy'];
            $this->przechwytywanie->PrzechwycOpisyNowychSprawZostawiajacZapisane($sprawy);
            $form['sprawy'] = $sprawy;
        }
        $this->przechwytywanie->przechwycRodzajDokumentuZtablicy($form);
        $this->przechwytywanie->PrzechwycNazweStronyIkierunekZtablicy($form);

        $this->opisStr = $form['opis'];
        $form['opis'] = $this->staryOpisStr;//udajemy, że opis został nie zmieniony
        
        $event->setData($form);
        


    }
    public function onSubmit(FormEvent $event)
    {
        //w tym miejscu dane są już ustawione w obiekcie
        // echo "onSubmit";
        $pismo = $event->getData();
        // $staryOpis = 
        // $pismo->setOpis($this->staryOpisStr);
        $pismo->setOpisJesliZmieniony($this->opisStr);
        $pismo->UtworzIdodajNoweSprawyWgOpisow($this->przechwytywanie->PrzechwyconeOpisySpraw());
        $pismo->UtworzIustawNowyRodzaj($this->przechwytywanie->PrzechwyconyRodzajDokumentu());
        $pismo->UtworzStroneZnazwyIkierunku($this->przechwytywanie->PrzechwyconaNazwaStrony(),$this->przechwytywanie->PrzechwyconyKierunek());
        
        $oznaczenieUzytkownika = $pismo->getOznaczenie();
        $pismo->setOznaczenie($pismo->OznaczenieKonwertujDlaBazy($oznaczenieUzytkownika));

        $event->setData($pismo);

    }
    public function onPostSubmit(FormEvent $event)
    {
    }

}