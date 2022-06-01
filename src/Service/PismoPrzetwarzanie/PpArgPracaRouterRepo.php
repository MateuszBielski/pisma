<?php

namespace App\Service\PismoPrzetwarzanie;

use App\Repository\PismoRepository;
use App\Service\PracaNaPlikach;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PpArgPracaRouterRepo implements PismoPrzetwarzanieArgumentyInterface
{
    private array $argumenty;

    public function __construct(PracaNaPlikach $pnp, UrlGeneratorInterface $router, PismoRepository $pr)
    {
        $this->argumenty = [
            'pnp' => $pnp,
            'router' => $router,
            'pismoRepository' => $pr,
        ];
    }

    public function Argumenty(): array
    {
        return $this->argumenty;
    }
}
