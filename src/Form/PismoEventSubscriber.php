<?php

namespace App\Form;

use App\Service\PrzechwytywanieZselect2;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PismoEventSubscriber implements EventSubscriberInterface
{
    private $przechwytywanie;
    private $opisStr;
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
        // $this->pismo = $event->getData();
        // $form = $event->getForm();

        
    }

    public function onPostSetData(FormEvent $event)
    {
        //dane odczytane z obiektu, ustawione w formularzu
        //nowe, wpisane w formularz dane nie są jeszcze dostępne
    }

    public function onPreSubmit(FormEvent $event): void
    {
        //nowe dane z formularza są już dostępne, nie ma dostępu do aktulanej sprawy
        $form = $event->getData();
        $sprawy = $form['sprawy'];
        $this->przechwytywanie = new PrzechwytywanieZselect2;
        $this->przechwytywanie->PrzechwycOpisyNowychSprawDlaPisma($sprawy);

        $this->opisStr = $form['opis'];
        $form['opis'] = '';
        $form['sprawy'] = $sprawy;

        $event->setData($form);
        // print_r($sprawy);
        // $form = $event->getForm();

    }
    public function onSubmit(FormEvent $event)
    {
        //w tym miejscu dane są już ustawione w obiekcie
        // $event->setData(coś);
        $pismo = $event->getData();
        $pismo->setOpis($this->opisStr);
        $pismo->UtworzIdodajNoweSprawyWgOpisow($this->przechwytywanie->PrzechwyconeOpisySpraw());

        echo $pismo->getOpis();
        // $event->setData($pismo);
        // foreach($event->getData()->getSprawy() as $s)
        // echo $s->getOpis();
    }
    public function onPostSubmit(FormEvent $event)
    {
    }
}