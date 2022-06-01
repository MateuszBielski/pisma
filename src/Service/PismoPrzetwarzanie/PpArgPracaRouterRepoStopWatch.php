<?php

namespace App\Service\PismoPrzetwarzanie;

use App\Repository\PismoRepository;
use App\Service\PracaNaPlikach;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class PpArgPracaRouterRepoStopWatch implements PismoPrzetwarzanieArgumentyInterface
{
    private array $argumenty;

    public function __construct(PracaNaPlikach $pnp, UrlGeneratorInterface $router, PismoRepository $pr, Stopwatch $sw)
    {
        $this->argumenty = [
            'pnp' => $pnp,
            'router' => $router,
            'pismoRepository' => $pr,
            'stopWatch' => $sw,
        ];
    }

    public function Argumenty(): array
    {
        return $this->argumenty;
    }
}
