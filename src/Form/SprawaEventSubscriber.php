<?php

namespace App\Form;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SprawaEventSubscriber implements EventSubscriberInterface
{
    private $sprawa;
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
        $this->sprawa = $event->getData();
        $form = $event->getForm();
        
    }

    public function onPostSetData(FormEvent $event)
    {
        //dane odczytane z obiektu, ustawione w formularzu
        //nowe, wpisane w formularz dane nie są jeszcze dostępne
    }

    public function onPreSubmit(FormEvent $event): void
    {
        //nowe dane z formularza są już dostępne, nie ma dostępu do aktulanej sprawy
        $opisStr = $event->getData()['opis'];
        // $form = $event->getForm();

        $this->sprawa->setOpisJesliZmieniony($opisStr);
    }
    public function onSubmit(FormEvent $event)
    {
        //w tym miejscu dane są już ustawione w obiekcie sprawa
        $event->setData($this->sprawa);
    }
    public function onPostSubmit(FormEvent $event)
    {
    }
}