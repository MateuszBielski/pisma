<?php

namespace App\Service\PismoPrzetwarzanie;

use App\Service\PracaNaPlikach;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PpArgPracaRouter implements PismoPrzetwarzanieArgumentyInterface
{
    private array $argumenty;

    public function __construct(PracaNaPlikach $pnp, UrlGeneratorInterface $router)
    {
        $this->argumenty = [
            'pnp' => $pnp,
            'router' => $router,
        ];
    }

    public function Argumenty(): array
    {
        return $this->argumenty;
    }
}
