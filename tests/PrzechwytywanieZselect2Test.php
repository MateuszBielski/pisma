<?php

namespace App\Tests;

use App\Entity\Kontrahent;
use App\Entity\Pismo;
use App\Entity\RodzajDokumentu;
use App\Service\EntityManagerMock;
use App\Service\EntityManagerWrapper;
use App\Service\PrzechwytywanieZselect2;
use Doctrine\ORM\EntityManager;
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
        $emMock = new EntityManagerMock();
        $przechwytywanie->przechwyconaNazweStronyDlaPismaUtrwal($pismo,$emMock);
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
        $emMock = new EntityManagerMock();
        $przechwytywanie->przechwyconaNazweStronyDlaPismaUtrwal($pismo,$emMock);
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
        $emMock = new EntityManagerMock();
        $przechwytywanie->przechwyconaNazweStronyDlaPismaUtrwal($pismo,$emMock);
        $this->assertTrue($emMock->usedPersist);
    }

    public function testPrzechwyconyRodzajDokumentuDlaPisma_zerujeJesliParametrTekstowy(): void
    {
        $rPismo = [];
        $rPismo['rodzaj'] = 'nowaNazwa';
        $request = new Request();
        $request->request->set('pismo',$rPismo);

        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycRodzajDokumentuDlaPisma($request);
        $this->assertEquals(null,$request->request->get('pismo')['rodzaj']);
    }
    public function testPrzechwyconyRodzajDokumentuDlaPisma_ZachowujeJesliParametrLiczbowy(): void
    {
        $rPismo = [];
        $rPismo['rodzaj'] = 3;
        $request = new Request();
        $request->request->set('pismo',$rPismo);

        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycRodzajDokumentuDlaPisma($request);
        $this->assertEquals(3,$request->request->get('pismo')['rodzaj']);
    }
    public function testPrzechwyconyRodzajDokumentuDlaPisma_ZachowujeJesliWartoscZerowejDlugosci(): void
    {
        $rPismo = [];
        $rPismo['rodzaj'] = "";
        $request = new Request();
        $request->request->set('pismo',$rPismo);

        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycRodzajDokumentuDlaPisma($request);
        $this->assertEquals('',$request->request->get('pismo')['rodzaj']);
    }

    public function testPrzechwyconyRodzajDokumentuDlaPismaUtrwal_ustawiaWobiekcie()
    {
        $pismo = new Pismo;
        $kPrzedZmiana = new RodzajDokumentu;
        $kPrzedZmiana->setNazwa('przedZmiana');
        $pismo->setRodzaj($kPrzedZmiana);
        
        $rPismo = [];
        $rPismo['rodzaj'] = 'poZmianie';
        $request = new Request();
        $request->request->set('pismo',$rPismo);


        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycRodzajDokumentuDlaPisma($request);
        $emMock = new EntityManagerMock();
        $przechwytywanie->przechwyconyRodzajDokumentuDlaPismaUtrwal($pismo,$emMock);
        $this->assertEquals('poZmianie',$pismo->getRodzaj()->getNazwa());
    }
    
    public function testPrzechwyconyRodzajDokumentuDlaPismaUtrwal_nieUstawiaJesliLiczbowa()
    {
        $pismo = new Pismo;
        $kPrzedZmiana = new RodzajDokumentu;
        $kPrzedZmiana->setNazwa('przedZmiana');
        $pismo->setRodzaj($kPrzedZmiana);
        
        $rPismo = [];
        $rPismo['rodzaj'] = 7;
        $request = new Request();
        $request->request->set('pismo',$rPismo);


        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycRodzajDokumentuDlaPisma($request);
        $emMock = new EntityManagerMock();
        $przechwytywanie->przechwyconyRodzajDokumentuDlaPismaUtrwal($pismo,$emMock);
        $this->assertEquals('przedZmiana',$pismo->getRodzaj()->getNazwa());
    }
    
    public function testPrzechwyconyRodzajDokumentuDlaPismaUtrwal_entityManagerPersist()
    {
        $pismo = new Pismo;
        $kPrzedZmiana = new RodzajDokumentu;
        $kPrzedZmiana->setNazwa('przedZmiana');
        $pismo->setRodzaj($kPrzedZmiana);
        
        $rPismo = [];
        $rPismo['rodzaj'] = 'nowyRodzaj';
        $request = new Request();
        $request->request->set('pismo',$rPismo);


        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycRodzajDokumentuDlaPisma($request);
        $emMock = new EntityManagerMock();
        $przechwytywanie->przechwyconyRodzajDokumentuDlaPismaUtrwal($pismo,$emMock);
        $this->assertTrue($emMock->usedPersist);
    }
    
}
