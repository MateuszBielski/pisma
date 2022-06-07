<?php

namespace App\Service\GeneratorPodgladuOdt;

class GeneratorPodgladuOdtMock extends GeneratorPodgladuOdt
{
    private bool $wykonajWywolane = false;
    public function Wykonaj()
    {
        $this->wykonajWywolane = true;
    }
    public function WykonajWywolane(): bool
    {
        return $this->wykonajWywolane;
    }
}
