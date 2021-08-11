<?php

namespace App\Form;

use App\Service\PrzechwytywanieZselect2;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class WyszukiwanieDokumentowEventSubscriber implements EventSubscriberInterface
{
    
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
        $wyszukiwanie = $event->getData();
        if(!$wyszukiwanie->UstawioneRepo())return;
        $wyszukiwanie->WyszukajDokumenty();
        $wyszukiwanie->UstalZakresDatWyszukanychDokumentow($wyszukiwanie->WyszukaneDokumenty());
        $event->setData($wyszukiwanie);
        
    }

    public function onPostSetData(FormEvent $event)
    {
        //dane odczytane z obiektu, ustawione w formularzu
        //nowe, wpisane w formularz dane nie są jeszcze dostępne

    }

    public function onPreSubmit(FormEvent $event): void
    {
        //nowe dane z formularza są już dostępne, nie ma dostępu do aktulanego obiektu
       
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