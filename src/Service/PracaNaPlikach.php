<?php

namespace App\Service;

use App\Entity\DokumentOdt;
use App\Entity\Pismo;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PracaNaPlikach
{
    private $odczytaneWszystkieNazwy;
    private $folderOdczytu;
    private $domyslnyFolderZplikami;
    protected $uruchomienie;
    private UrlGeneratorInterface $router;
    private string $rozszerzeniePliku = '';
    protected string $folderPodgladu = '';

    public function __construct(UrlGeneratorInterface $router = null, $domyslnyFolderZplikami = null)
    {
        if (isset($router))
            $this->router = $router;
        $this->domyslnyFolderZplikami = $domyslnyFolderZplikami;
    }

    public function PobierzWszystkieNazwyPlikowZfolderu(string $path): array
    {


        $nazwy = @array_diff(@scandir($path), array('..', '.'));
        
        if (!count($nazwy)) {
            $nazwy[] = "folder $path jest pusty";
            return $nazwy;
        }
        $pathZukos = $path;
        if (substr($path, -1) != '/') $pathZukos .= '/';
        foreach ($nazwy as $k => &$n) {
            $n = $pathZukos . $n;
            if(is_dir($n))unset($nazwy[$k]);
        }
        $this->odczytaneWszystkieNazwy = $nazwy;
        $this->folderOdczytu = $path;
        return $nazwy;
    }
    private function WydzielPlikiZrozszerzeniem(array $nazwy, string $rozsz): array
    {
        $nazwyFiltr = [];
        foreach ($nazwy as $n) {
            $arr = explode('.', $n);
            $extension = end($arr);
            if ($rozsz == $extension) $nazwyFiltr[] = $n;
        }
        return $nazwyFiltr;
    }
    private function ObetnijSciezke(array $nazwy): array
    {
        $result = [];
        foreach ($nazwy as $n) {
            $arr = explode('/', $n);
            $result[] = end($arr);
        }
        return $result;
    }
    public function NazwyZrozszerzeniem(string $rozsz): array
    {
        $nazwyFiltr = $this->WydzielPlikiZrozszerzeniem($this->odczytaneWszystkieNazwy, $rozsz);
        if (!count($nazwyFiltr)) $nazwyFiltr[] = "folder $this->folderOdczytu nie zawiera plik??w ." . $rozsz;
        return $nazwyFiltr;
    }
    public function NazwyBezSciezkiZrozszerzeniem(string $rozsz): array
    {
        $nazwy = $this->ObetnijSciezke($this->WydzielPlikiZrozszerzeniem($this->odczytaneWszystkieNazwy, $rozsz));
        if (!count($nazwy)) $nazwy[] = "folder $this->folderOdczytu nie zawiera plik??w ." . $rozsz;
        return $nazwy;
    }
    public function UtworzPismoNaPodstawie($folder, $nazwaZrodla): Pismo
    {
        if (substr($folder, -1) != '/') $folder .= '/';
        $arr = explode('/', $nazwaZrodla);
        $zrodlo = count($arr) ? end($arr) : $nazwaZrodla;
        $path = $folder . $zrodlo;
        //poni??sze powoduje wiele b????d??w we wcze??niejszych testach
        // if(!file_exists($path)) throw new Exception('Plik: '.$path.' nie istnieje');
        $RodzajDokumentu = Pismo::class;

        $rozsz = pathinfo($path, PATHINFO_EXTENSION);
        $this->rozszerzeniePliku = $rozsz;
        if ($rozsz == "odt") $RodzajDokumentu = DokumentOdt::class;

        $pismo = new $RodzajDokumentu($path);
        if ($this->domyslnyFolderZplikami != null && $this->domyslnyFolderZplikami != $folder)
            $pismo->DodawajDoNazwyZakodowanaSciezke();
        if (isset($this->router)) $pismo->setRouter($this->router);
        return $pismo;
    }
    public function RozszerzeniePliku()
    {
        return $this->rozszerzeniePliku;
    }
    public function UtworzPismaZfolderu(string $folder, $rozsz = ""): array
    {
        $pisma = [];
        $this->PobierzWszystkieNazwyPlikowZfolderu($folder);
        $nazwy = (strlen($rozsz) ? $this->NazwyBezSciezkiZrozszerzeniem($rozsz) : $this->odczytaneWszystkieNazwy) ?? [];
        foreach ($nazwy as $n)
            $pisma[] = $this->UtworzPismoNaPodstawie($this->folderOdczytu, $n);
        return $pisma;
    }
    public function getFolderDlaPlikowPodgladu()
    {
        return $this->folderPodgladu;
    }
    public function GenerujPodgladJesliNieMaDlaPisma(string $folderPng, Pismo $pismo)
    {
        if ($folderPng == '') throw new Exception('nale??y zapewni?? folder dla plik??w png');
        $this->folderPodgladu = $folderPng;
        $pathFolderDlaJednegoDokumentu =  $folderPng . $pismo->NazwaZrodlaBezRozszerzenia();

        if (!file_exists($pathFolderDlaJednegoDokumentu)) {
            mkdir($pathFolderDlaJednegoDokumentu, 0777, true);
            $zrodlo = $pismo->getAdresZrodlaPrzedZarejestrowaniem();
            $cel = $pismo->FolderZpodlgademPngWzglednieZgodnieZeZrodlem() . $pismo->NazwaZrodlaBezRozszerzenia();
            if ($this->uruchomienie)
                $this->uruchomienie->UruchomPolecenie(['pdftopng', $zrodlo, $cel]);
        }
    }
    public function PrzeniesPlikiPdfiPodgladu(string $sciezkaDoZarejestrowanych, Pismo $pismo): bool
    {
        $adresZrodla = $pismo->getAdresZrodlaPrzedZarejestrowaniem();
        $nazwaPliku = $pismo->getNazwaPliku();
        $jestPodglad = $pismo->JestPodgladDlaZrodla();
        $adresPlikuPoZarejestrowaniu = $sciezkaDoZarejestrowanych . $nazwaPliku;
        if (!file_exists($adresZrodla)) return false;
        //$przeniesioneZrodlo = rename($adresZrodla,$adresPlikuPoZarejestrowaniu);//nie chce dzia??a?? na dysku zamontowanym z Windowsa
        $przeniesioneZrodlo = false;
        if (copy($adresZrodla, $adresPlikuPoZarejestrowaniu)) {
            unlink($adresZrodla);
            $przeniesioneZrodlo = true;
        }
        if ($jestPodglad && $przeniesioneZrodlo) {
            $sciezkiZrodla = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem(false);
            $sciezkiPoRejestracji = $pismo->GenerujNazwyDocelowychPodgladowZeSciezkamiWfolderzeZrodlowym();
            $i = 0;
            foreach ($sciezkiZrodla as $sz) {
                rename($sz, $sciezkiPoRejestracji[$i++]);
            }
            $folderPodgladuPrzedRejestracja = $pismo->FolderZpodlgademPngWzglednieZgodnieZeZrodlem();
            $folderPodgladuPoZarejestrowaniu = $pismo->FolderZpodlgademPngWzglednieZgodnieZnazwaPliku();
            $zmienionyFolder = rename($folderPodgladuPrzedRejestracja, $folderPodgladuPoZarejestrowaniu);

            return true;
        }
        return $przeniesioneZrodlo;
    }
    public function UaktualnijNazwyPlikowPodgladu(Pismo $pismo)
    {
        $staraNazwa = $pismo->getNazwaPlikuPrzedZmiana();
        $nowaNazwa = $pismo->getNazwaPliku();
        if ($staraNazwa && $staraNazwa != $nowaNazwa) {
            $sciezkiZrodla = $pismo->SciezkiDoPlikuPodgladowDlaNazwyPrzedZmiana(false);
            $sciezkiPoZmianie = $pismo->GenerujNazwyDocelowychPodgladowZeSciezkamiWfolderzePrzedZmiana();
            $i = 0;
            foreach ($sciezkiZrodla as $sz) {
                rename($sz, $sciezkiPoZmianie[$i++]);
            }
            $folderPodgladuPrzedZmiana = $pismo->FolderZpodlgademPngWzglednieZgodnieZnazwaPrzedZmiana();
            $folderPodgladuPoZmianie = $pismo->FolderZpodlgademPngWzglednieZgodnieZnazwaPliku();
            $zmienionyFolder = rename($folderPodgladuPrzedZmiana, $folderPodgladuPoZmianie);
        }
    }
    public function UaktualnijNazwePlikuPdf(string $folderPdf, Pismo $pismo)
    {
        $nazwaStara = $folderPdf . $pismo->getNazwaPlikuPrzedZmiana();
        $nazwaNowa = $folderPdf . $pismo->getNazwaPliku();

        if (strlen($pismo->getNazwaPlikuPrzedZmiana()) && $nazwaStara != $nazwaNowa)
            rename($nazwaStara, $nazwaNowa);
    }
    public function setUruchomienieProcesu(UruchomienieProcesu $uruchomienie)
    {
        $this->uruchomienie = $uruchomienie;
    }
    public function PobierzWszystkieNazwyFolderowZfolderu(string $sciezkaDoFolderu)
    {
        $nazwy = @array_diff(@scandir($sciezkaDoFolderu), array('..', '.')) ?? [];
        $foldery = [];
        foreach ($nazwy as $n) {
            $nn = $sciezkaDoFolderu . "/" . $n;
            if (is_dir($nn)) $foldery[] = "/" . $n;
        }
        return $foldery;
    }
    public function ZfolderuPobierzNazwyFolderowZakonczoneUkosnikiem(string $sciezkaDoFolderu)
    {

        $arr = $this->PobierzWszystkieNazwyFolderowZfolderu($sciezkaDoFolderu);

        return array_map(fn ($f) => ltrim($f, "/") . "/", $arr);
    }
    public function ZeSciezkiObetnijPrzedOstatnimUkosnikiem(string $sciezka): string
    {
        $pozycja = strrpos($sciezka, "/");
        return substr($sciezka, 0, $pozycja);
    }
    public function NajglebszyMozliwyFolderZniepelnejSciezki(string $niepelnaScezka): string
    {
        $gotowaSciezka = $niepelnaScezka; // . "/";
        while (strlen($niepelnaScezka)) {
            if (is_dir($niepelnaScezka)) return $gotowaSciezka;
            $niepelnaScezka = $this->ZeSciezkiObetnijPrzedOstatnimUkosnikiem($niepelnaScezka);
            $gotowaSciezka = $niepelnaScezka;
        }
        return "/";
    }
    public function CzescSciezkiZaFolderem($sciezkaNiepelna, $sciezkaOstatniegoFolderu): string
    {
        if ($sciezkaOstatniegoFolderu === "/") return $sciezkaNiepelna;
        return substr($sciezkaNiepelna, strlen($sciezkaOstatniegoFolderu));
    }
    public function FitrujFolderyPasujaceDoFrazy($foldery, $sciezkaPozostaloscDoWyszukania)
    {
        if (!strlen($sciezkaPozostaloscDoWyszukania)) return $foldery;
        $pattern = "/^\\" . $sciezkaPozostaloscDoWyszukania . "/i";
        // podw??jny lewy uko??nik na pocz??tku jest dla zabezpieczenia uko??nika prawego 
        //od kt??rego rozpoczyna si?? $sciezka....
        //caseInsensitive "/i" case sensitive "/"
        return array_values(preg_grep($pattern, $foldery)) ?? [];
    }
}
