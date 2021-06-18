<?php

namespace App\Tests;

use App\Entity\Kontrahent;
use App\Entity\Pismo;
use App\Service\PrzechwytywanieZselect2;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class PrzechwytywanieZselect2Test extends TestCase
{
    public function testPrzechwyc_zerujeJesliParametrTekstowy(): void
    {
        $nazwaObiektu = 'nObiekt';
        $nazwaPola = 'nPole';
        $wartoscPola = 'nowaNazwa';
        $tablObiekt = [];
        $tablObiekt[$nazwaPola] = $wartoscPola;
        $request = new Request();
        $request->request->set($nazwaObiektu,$tablObiekt);

        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwyc($request,['nObiekt' => 'nPole']);
        $this->assertEquals(null,$request->request->get('nObiekt')['nPole']);
    }
    public function testPrzechwyc_ZachowujeJesliParametrLiczbowy(): void
    {
        $nazwaObiektu = 'nObiekt';
        $nazwaPola = 'nPole';
        $wartoscPola = 8;
        $tablObiekt = [];
        $tablObiekt[$nazwaPola] = $wartoscPola;
        $request = new Request();
        $request->request->set($nazwaObiektu,$tablObiekt);

        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwyc($request,['nObiekt' => 'nPole']);
        $this->assertEquals(8,$request->request->get('nObiekt')['nPole']);
    }
    public function testPrzechwyc_ZachowujeJesliWartoscZerowejDlugosci(): void
    {
        $nazwaObiektu = 'nObiekt';
        $nazwaPola = 'nPole';
        $wartoscPola = "";
        $tablObiekt = [];
        $tablObiekt[$nazwaPola] = $wartoscPola;
        $request = new Request();
        $request->request->set($nazwaObiektu,$tablObiekt);

        $przechwytywanie = new PrzechwytywanieZselect2;
        // $przechwytywanie->przechwyc($request,['nObiekt' => 'nPole']);        
        $przechwytywanie->przechwyc($request,[$nazwaObiektu => $nazwaPola]);

        $this->assertEquals('',$request->request->get('nObiekt')['nPole']);
    }
    public function testPrzechwycNazweStronyDlaPisma_zerujeJesliParametrTekstowy(): void
    {
        $rPismo = [];
        $rPismo['strona'] = 'nowaNazwa';
        $request = new Request();
        $request->request->set('pismo',$rPismo);

        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycNazweStronyDlaPisma($request);
        $this->assertEquals(null,$request->request->get('pismo')['strona']);
    }
    public function testPrzechwycNazweStronyDlaPisma_ZachowujeJesliParametrLiczbowy(): void
    {
        $rPismo = [];
        $rPismo['strona'] = 8;
        $request = new Request();
        $request->request->set('pismo',$rPismo);

        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycNazweStronyDlaPisma($request);
        $this->assertEquals(8,$request->request->get('pismo')['strona']);
    }
    public function testPrzechwycNazweStronyDlaPisma_ZachowujeJesliWartoscZerowejDlugosci(): void
    {
        $rPismo = [];
        $rPismo['strona'] = "";
        $request = new Request();
        $request->request->set('pismo',$rPismo);

        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycNazweStronyDlaPisma($request);
        $this->assertEquals('',$request->request->get('pismo')['strona']);
    }

    public function testPrzechwyconaNazweStronyDlaPismaUtrwal_ustawiaWobiekcie()
    {
        $pismo = new Pismo;
        $kPrzedZmiana = new Kontrahent;
        $kPrzedZmiana->setNazwa('przedZmiana');
        $pismo->setStrona($kPrzedZmiana);
        
        $rPismo = [];
        $rPismo['strona'] = 'poZmianie';
        $request = new Request();
        $request->request->set('pismo',$rPismo);


        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycNazweStronyDlaPisma($request);
        $przechwytywanie->przechwyconaNazweStronyDlaPismaUtrwal($pismo);
        $this->assertEquals('poZmianie',$pismo->getStrona()->getNazwa());
    }
    public function testPrzechwyconaNazweStronyDlaPismaUtrwal_nieUstawiaJesliLiczbowa()
    {
        $pismo = new Pismo;
        $kPrzedZmiana = new Kontrahent;
        $kPrzedZmiana->setNazwa('przedZmiana');
        $pismo->setStrona($kPrzedZmiana);
        
        $rPismo = [];
        $rPismo['strona'] = 7;
        $request = new Request();
        $request->request->set('pismo',$rPismo);


        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycNazweStronyDlaPisma($request);
        $przechwytywanie->przechwyconaNazweStronyDlaPismaUtrwal($pismo);
        $this->assertEquals('przedZmiana',$pismo->getStrona()->getNazwa());
    }

    public function testPrzechwyconaNazweStronyDlaPismaUtrwal_entityManagerPersist()
    {
        $pismo = new Pismo;
        $kPrzedZmiana = new Kontrahent;
        $kPrzedZmiana->setNazwa('przedZmiana');
        $pismo->setStrona($kPrzedZmiana);
        
        $rPismo = [];
        $rPismo['strona'] = 'nowyKontrahent';
        $request = new Request();
        $request->request->set('pismo',$rPismo);


        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycNazweStronyDlaPisma($request);
        $przechwytywanie->przechwyconaNazweStronyDlaPismaUtrwal($pismo);
        $this->assertEquals('przedZmiana',$pismo->getStrona()->getNazwa());
    }
    public function _testPrzechwyconeUtrwal_ustawiaWtymObiekcieKtorymTrzeba()
    {
        // $pismo = new Pismo;
        // $pismo->setStrona()
        // $this->assertEquals();
    }
}
