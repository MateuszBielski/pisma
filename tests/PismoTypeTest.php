<?php

namespace App\Tests;

use App\Entity\Pismo;
use App\Form\PismoType;
// use PHPUnit\Framework\TestCase;
// use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;

class PismoTypeTest extends KernelTestCase
{
    private array $parametryRequestu = ['pismo' => [
        'opis' => 'jakis opis',
        'strona' => 'nazwaStrony',
        'rodzaj' => 'rodzajDokumentuUstalony',
        'sprawy' => ['sprawa3', 'sprawa7'],
        'dataDokumentu' => ['year' => 2019, 'month' => 11, 'day' => 24],
    ]];
    public function testPrzechwycenieNazwySprawy()
    {
        $pismo = new Pismo('dokument.pdf');
        $form = $this->Formularz(PismoType::class, $pismo);

        $parametry = $this->parametryRequestu;
        $parametry['pismo']['sprawy'] = ['sprawa4', 'sprawa6'];
        $form->handleRequest($this->UtworzIUstawRequestPost($parametry));
        $this->assertEquals('sprawa4', $pismo->getSprawy()[0]->getNazwa());
    }
    public function testPrzechwycenieRodzajuDokumentu()
    {
        $pismo = new Pismo('dokument.pdf');
        $form = $this->Formularz(PismoType::class, $pismo);

        $parametry = $this->parametryRequestu;
        $parametry['pismo']['rodzaj'] = 'zapytanie';
        $form->handleRequest($this->UtworzIUstawRequestPost($parametry));
        $this->assertEquals('zapytanie', $pismo->getRodzaj()->getNazwa());
    }
    public function testPrzechwycenieNadawcy()
    {
        $pismo = new Pismo('dokument.pdf');
        $form = $this->Formularz(PismoType::class, $pismo);

        $parametry = $this->parametryRequestu;
        $parametry['pismo']['strona'] = 'jakisNadawca';
        $parametry['pismo']['kierunek'] = 1;
        $form->handleRequest($this->UtworzIUstawRequestPost($parametry));
        $this->assertEquals('jakisNadawca', $pismo->getNadawca()->getNazwa());
    }
    public function testPrzechwycenieOdbiorcy()
    {
        $pismo = new Pismo('dokument.pdf');
        $form = $this->Formularz(PismoType::class, $pismo);

        $parametry = $this->parametryRequestu;
        $parametry['pismo']['strona'] = 'jakisOdbiorca';
        $parametry['pismo']['kierunek'] = 2;
        $form->handleRequest($this->UtworzIUstawRequestPost($parametry));
        $this->assertEquals('jakisOdbiorca', $pismo->getOdbiorca()->getNazwa());
    }
    private function Formularz(string $type, $model): FormInterface
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        $formFactory = $container->get("form.factory");

        return $formFactory->create(PismoType::class, $model);
    }
    private function UtworzIUstawRequestPost(array $parametry): Request
    {
        $request = new Request();
        $request->server->set("REQUEST_METHOD", 'POST'); //dzięki temu wywoływana jest metoda onPreSubmit, dla Get jest pomijana
        foreach ($parametry as $k => $val)
            $request->request->set($k, $val);
        return $request;
    }
}
