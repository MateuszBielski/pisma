<?php

namespace App\Form;

use App\Service\PrzechwytywanieZselect2;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Stopwatch\Stopwatch;

class WyszDokEventSubsc implements EventSubscriberInterface
{
    private $wyszukiwanie;
    private $formularz;
    public function __construct(Stopwatch $sw)
    {
        $this->stopwatch = $sw;
    }
    
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
        $this->stopwatch->start('onPreSubmit');
        if(!$this->wyszukiwanie->UstawioneRepo())return;
        $event->setData($this->wyszukiwanie->onPreSubmit($event->getData()));
        $this->stopwatch->stop('onPreSubmit');
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