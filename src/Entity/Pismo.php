<?php

namespace App\Entity;

use App\Repository\PismoRepository;
use App\Service\KonwOpis_Str_Acoll;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @ORM\Entity(repositoryClass=PismoRepository::class)
 * @ORM\Table(name="pismo",uniqueConstraints={@ORM\UniqueConstraint(name="nazwa_pliku_unikalna", columns={"nazwa_pliku"})})
 * @UniqueEntity("nazwaPliku",
 *     message="Dokument o tej nazwie został już zarejestrowany")
 * 
 */
// * @ORM\InheritanceType("JOINED")
// * @ORM\DiscriminatorColumn(name="discriminator")
class Pismo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     
     * 
     */
    protected $nazwaPliku;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $oznaczenie = '';

    protected $adresZrodlaPrzedZarejestrowaniem;
    private $dataModyfikacji;
    private $folderPodgladu = 'png/';
    private $nazwaPlikuPrzedZmiana = '';
    private $sciezkaDoFolderuPdf = "";
    private $sciezkaGenerUrl;
    private $nazwyOdczytaneZfolderu = null;

    /**
     * @ORM\ManyToMany(targetEntity=Sprawa::class, inversedBy="dokumenty", cascade={"persist"})
     * 
     */
    //usuwam ,"remove" z cascade
    private $sprawy;

    /**
     * @ORM\ManyToOne(targetEntity=Kontrahent::class, inversedBy="odeMnie", cascade={"persist"})
     */
    private $nadawca;

    /**
     * @ORM\ManyToOne(targetEntity=Kontrahent::class, inversedBy="doMnie", cascade={"persist"})
     */
    private $odbiorca;

    /**
     * @ORM\ManyToOne(targetEntity=RodzajDokumentu::class, inversedBy="dokumenty", cascade={"persist"})
     */
    private $rodzaj;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dataDokumentu = null;


    private $kierunek = 1;


    private $strona;

    /**
     * @ORM\OneToMany(targetEntity=WyrazWciagu::class, mappedBy="pismo", cascade={"persist", "remove"})
     */
    private $opis;
    // private $nazwaZrodlaPrzedZarejestrowaniem;
    private $konw;
    private $niepotrzebneWyrazy = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $opisCiag;

    private int $rozmiar = 0;

    protected UrlGeneratorInterface $router;
    protected int $numerStrony = 1;

    public function __construct(string $adresZrodlaPrzedZarejestrowaniem = "")
    {
        $this->adresZrodlaPrzedZarejestrowaniem = $adresZrodlaPrzedZarejestrowaniem;
        // $this->dataModyfikacji = @date("Y-m-d H:i", @filemtime($adresZrodlaPrzedZarejestrowaniem));
        $timestamp = @filemtime($adresZrodlaPrzedZarejestrowaniem);
        $this->dataDokumentu = new DateTime();
        $this->dataDokumentu->setTimestamp($timestamp);
        $this->dataModyfikacji = @date("Y-m-d", $timestamp);
        $this->nazwaPliku = $this->getNazwaZrodlaPrzedZarejestrowaniemObcietaSciezka();
        $this->sprawy = new ArrayCollection();
        $this->opis = new ArrayCollection();
        $this->rozmiar = @filesize($adresZrodlaPrzedZarejestrowaniem);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNazwaPliku(): ?string
    {
        return $this->nazwaPliku;
    }

    public function setNazwaPliku(?string $nazwaPliku): self
    {
        $this->nazwaPlikuPrzedZmiana = $this->nazwaPliku;
        $this->nazwaPliku = $nazwaPliku;
        return $this;
    }
    public function setSciezkaDoFolderuPdf(string $path)
    {
        $this->sciezkaDoFolderuPdf = $path;
    }

    public function getOznaczenie(): ?string
    {
        return $this->oznaczenie;
    }

    public function setOznaczenie(?string $oznaczenie): self
    {
        if ($oznaczenie == null) $oznaczenie = '';
        $this->oznaczenie = $oznaczenie;

        return $this;
    }
    public function getNazwaZrodlaPrzedZarejestrowaniemObcietaSciezka(): string
    {
        $arr = explode('/', $this->adresZrodlaPrzedZarejestrowaniem);
        return end($arr);
    }
    public function NazwaSkroconaZrodla(int $dlugosc): string
    {
        $skrot = $this->getNazwaZrodlaPrzedZarejestrowaniemObcietaSciezka();
        $przod = substr($skrot, 0, -4);
        // print("\n".strlen($przod)."   ".$dlugosc+1);
        // echo "\nprzod:  ".$przod." strlen: ".strlen($przod);
        if (strlen($przod) < $dlugosc + 3) return $skrot;

        $przod = substr($przod, 0, $dlugosc);
        $tyl = substr($skrot, -4);

        return $przod . ".." . $tyl;
    }
    public function getAdresZrodlaPrzedZarejestrowaniem()
    {
        return $this->adresZrodlaPrzedZarejestrowaniem;
    }
    public function SciezkaDoPlikuPierwszejStronyDuzegoPodgladuPrzedZarejestrowaniem(): string
    {
        $nazwaBezRozszerzenia = $this->NazwaZrodlaBezRozszerzenia();
        return "/" . $this->folderPodgladu . $nazwaBezRozszerzenia . "/" . $nazwaBezRozszerzenia . "-000001.png";
    }
    private function SciezkiPodgladowDlaNazwy(string $nazwa, $slashWiodacy = true, $zFoldermGlownym = true): array
    {
        $sciezki = [];
        // $nazwaBezRozszerzenia = $this->NazwaZrodlaBezRozszerzenia();
        $path = $this->folderPodgladu . $nazwa;
        if ($this->nazwyOdczytaneZfolderu === null) {
            $zawartoscFolderu = @scandir($path);
            if($zawartoscFolderu == false)$zawartoscFolderu = [];
            $this->nazwyOdczytaneZfolderu = @array_diff($zawartoscFolderu, array('..', '.'));
        }
        if (!$this->nazwyOdczytaneZfolderu || !count($this->nazwyOdczytaneZfolderu)) {
            $sciezki[] = "folder $path jest pusty";
            $this->nazwyOdczytaneZfolderu = [];
            return $sciezki;
        }
        if (!$zFoldermGlownym) $path = $nazwa;
        foreach ($this->nazwyOdczytaneZfolderu as $n) {
            $arr = explode('.', $n);
            $extension = end($arr);

            if ('png' == $extension) {

                $s = $path . "/" . $n;
                if ($slashWiodacy) $s = "/" . $s;

                // $s = $path."/".$n;
                $sciezki[] = $s;
                // echo "\n".$s;
            }
        }
        return $sciezki;
    }
    public function SciezkiDoPlikuPodgladowPrzedZarejestrowaniem($slashWiodacy = true): array
    {
        return $this->SciezkiPodgladowDlaNazwy($this->NazwaZrodlaBezRozszerzenia(), $slashWiodacy);
    }
    public function SciezkiDoPlikuPodgladowPrzedZarejestrowaniemBezFolderuGlownego()
    {
        return $this->SciezkiDoPlikuPodgladowZarejestrowanychBezFolderuGlownego();
    }
    public function SciezkiDoPlikuPodgladowDlaNazwyPrzedZmiana($slashWiodacy = true): array
    {
        $nazwaPlikuBezRozszerzenia = substr($this->nazwaPlikuPrzedZmiana, 0, strrpos($this->nazwaPlikuPrzedZmiana, '.'));
        // echo "\nXXXx  ".$this->nazwaPliku." yyy ".$nazwaPlikuBezRozszerzenia;
        return $this->SciezkiPodgladowDlaNazwy($nazwaPlikuBezRozszerzenia, $slashWiodacy);
    }

    public function GenerujNazwyZeSciezkamiDlaDocelowychPodgladow(): array
    {
        $ileStronPodgladu = count($this->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem());
        $nazwy = [];
        $nazwaPlikuBezRozszerzenia = substr($this->nazwaPliku, 0, strrpos($this->nazwaPliku, '.'));
        for ($i = 1; $i <= $ileStronPodgladu; $i++) {
            $nazwy[] = $this->folderPodgladu . $nazwaPlikuBezRozszerzenia . "/" . $nazwaPlikuBezRozszerzenia . "-00000" . $i . ".png";
        }
        return $nazwy;
    }
    public function GenerujNazwyDocelowychPodgladowZeSciezkamiWfolderzeZrodlowym()
    {
        $ileStronPodgladu = count($this->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem());
        $nazwy = [];
        $nazwaPlikuBezRozszerzenia = substr($this->nazwaPliku, 0, strrpos($this->nazwaPliku, '.'));
        $nazwaZrodlaPrzedZarejestrowaniem = $this->NazwaZrodlaBezRozszerzenia();
        for ($i = 1; $i <= $ileStronPodgladu; $i++) {
            // $nazwy[] = $this->folderPodgladu.$nazwaZrodlaPrzedZarejestrowaniem."/".$nazwaPlikuBezRozszerzenia."-00000".$i.".png";
            $nazwy[] = $this->folderPodgladu . $nazwaZrodlaPrzedZarejestrowaniem . "/" . $nazwaPlikuBezRozszerzenia . "-" . sprintf('%06s', $i) . ".png";
        }
        return $nazwy;
    }
    public function GenerujNazwyDocelowychPodgladowZeSciezkamiWfolderzePrzedZmiana()
    {
        $ileStronPodgladu = count($this->SciezkiDoPlikuPodgladowDlaNazwyPrzedZmiana());
        $nazwy = [];
        $nazwaBezRozszerzeniaPrzedZmiana = substr($this->nazwaPlikuPrzedZmiana, 0, strrpos($this->nazwaPlikuPrzedZmiana, '.'));
        $nazwaBezRozszerzeniaPoZmianie = substr($this->nazwaPliku, 0, strrpos($this->nazwaPliku, '.'));
        for ($i = 1; $i <= $ileStronPodgladu; $i++) {
            // $nazwy[] = $this->folderPodgladu.$nazwaZrodlaPrzedZarejestrowaniem."/".$nazwaPlikuBezRozszerzenia."-00000".$i.".png";
            $nazwy[] = $this->folderPodgladu . $nazwaBezRozszerzeniaPrzedZmiana . "/" . $nazwaBezRozszerzeniaPoZmianie . "-" . sprintf('%06s', $i) . ".png";
        }
        return $nazwy;
    }
    public function SciezkiDoPlikuPodgladowZarejestrowanych(): array
    {
        $nazwaPlikuBezRozszerzenia = substr($this->nazwaPliku, 0, strrpos($this->nazwaPliku, '.'));
        // echo "\nXXXx  ".$this->nazwaPliku." yyy ".$nazwaPlikuBezRozszerzenia;
        return $this->SciezkiPodgladowDlaNazwy($nazwaPlikuBezRozszerzenia);
    }
    public function SciezkiDoPlikuPodgladowZarejestrowanychBezFolderuGlownego(): array
    {
        $nazwaPlikuBezRozszerzenia = substr($this->nazwaPliku, 0, strrpos($this->nazwaPliku, '.'));
        // echo "\nXXXx  ".$this->nazwaPliku." yyy ".$nazwaPlikuBezRozszerzenia;
        return $this->SciezkiPodgladowDlaNazwy($nazwaPlikuBezRozszerzenia, false, false);
    }

    public function FolderZpodlgademPngWzglednieZgodnieZeZrodlem()
    {
        return $this->folderPodgladu . $this->NazwaZrodlaBezRozszerzenia() . "/";
    }
    public function FolderZpodlgademPngWzglednieZgodnieZnazwaPliku()
    {
        // $arr = explode('.',$this->nazwaPliku);

        $nazwaBezRozszerzenia =  substr($this->nazwaPliku, 0, strrpos($this->nazwaPliku, '.'));
        return $this->folderPodgladu . $nazwaBezRozszerzenia . "/";
    }
    public function FolderZpodlgademPngWzglednieZgodnieZnazwaPrzedZmiana()
    {
        $nazwaBezRozszerzenia =  substr($this->nazwaPlikuPrzedZmiana, 0, strrpos($this->nazwaPlikuPrzedZmiana, '.'));
        return $this->folderPodgladu . $nazwaBezRozszerzenia . "/";
    }
    public function NazwaZrodlaBezRozszerzenia(): string
    {
        $nazwa = $this->getNazwaZrodlaPrzedZarejestrowaniemObcietaSciezka();
        return substr($nazwa, 0, strrpos($nazwa, '.'));
    }
    public function getDataModyfikacji()
    {
        return $this->dataModyfikacji;
    }
    public function JestPodgladDlaZrodla(): bool
    {
        return file_exists($this->FolderZpodlgademPngWzglednieZgodnieZeZrodlem());
        // return false;
    }
    public function setFolderPodgladu(string $path)
    {
        $this->folderPodgladu = $path;
    }

    /**
     * @return Collection|Sprawa[]
     */
    public function getSprawy(): Collection
    {
        return $this->sprawy;
    }

    public function addSprawy(Sprawa $sprawy): self
    {
        if (!$this->sprawy->contains($sprawy)) {
            $this->sprawy[] = $sprawy;
            // $sprawy->addDokumenty($this);
        }

        return $this;
    }

    public function removeSprawy(Sprawa $sprawy): self
    {
        if ($this->sprawy->removeElement($sprawy)) {
            $sprawy->removeDokumenty($this);
        }

        return $this;
    }

    public function getNadawca(): ?Kontrahent
    {
        return $this->nadawca;
    }

    public function setNadawca(?Kontrahent $nadawca): self
    {
        if (!$nadawca) return $this;
        $this->nadawca = $nadawca;
        $this->odbiorca = null;
        $this->kierunek = 1;
        $this->strona = $nadawca;
        return $this;
    }

    public function getOdbiorca(): ?Kontrahent
    {
        return $this->odbiorca;
    }

    public function setOdbiorca(?Kontrahent $odbiorca): self
    {
        if (!$odbiorca) return $this;
        // echo "\nXXXXXXXsetOdbiorca";
        $this->odbiorca = $odbiorca;
        $this->nadawca = null;
        $this->kierunek = 2;
        $this->strona = $odbiorca;
        return $this;
    }

    public function getRodzaj(): ?RodzajDokumentu
    {
        return $this->rodzaj;
    }

    public function setRodzaj(?RodzajDokumentu $rodzaj): self
    {
        $this->rodzaj = $rodzaj;

        return $this;
    }
    public function getNazwaPlikuPrzedZmiana()
    {
        return $this->nazwaPlikuPrzedZmiana;
    }

    public function getDataDokumentu(): ?\DateTimeInterface
    {
        return $this->dataDokumentu;
    }

    public function setDataDokumentu(\DateTimeInterface $dataDokumentu): self
    {
        $this->dataDokumentu = $dataDokumentu;

        return $this;
    }
    public function UstawDateDokumentuNull()
    {
        $this->dataDokumentu = null;
    }
    public function UstalJesliTrzebaDateDokumentuZdatyMod(): bool
    {
        if ($this->dataDokumentu != null) return false;
        $this->dataDokumentu = new DateTime();
        $this->dataDokumentu->setTimestamp(@filemtime($this->sciezkaDoFolderuPdf . $this->nazwaPliku));
        return true;
    }


    public function UstalStroneNaPodstawieKierunku(?Kontrahent $strona, int $kierunek)
    {
        if ($kierunek == 1) {
            $this->nadawca = $strona;
            $this->odbiorca = null;
        }
        if ($kierunek == 2) {
            $this->nadawca = null;
            $this->odbiorca = $strona;
        }
    }
    public function UstalStroneIKierunek()
    {
        if ($this->nadawca) {
            $this->strona = $this->nadawca;
            $this->kierunek = 1;
            return;
        }
        if ($this->odbiorca) {
            $this->strona = $this->odbiorca;
            $this->kierunek = 2;
            return;
        }
    }
    public function UtworzStroneZnazwyIkierunku(string $nazwa, int $kierunek)
    {
        if(!strlen($nazwa))return;
        $this->strona = new Kontrahent;
        $this->strona->setNazwa($nazwa);
        $this->kierunek = $kierunek;
        $this->UstalStroneNaPodstawieKierunku($this->strona,$this->kierunek);
    }

    public function getKierunek(): ?int
    {
        return $this->kierunek;
    }
    public function getKierunekOpisowo()
    {
        switch ($this->kierunek) {
            case 1:
                return 'przychodzące od: ';
            case 2:
                return 'wychodzące do: ';
        }
    }

    public function setKierunek(?int $kierunek): self
    {
        $this->kierunek = $kierunek;
        if ($this->strona)
            $this->UstalStroneNaPodstawieKierunku($this->strona, $kierunek);
        return $this;
    }

    public function getStrona(): ?Kontrahent
    {
        return $this->strona;
    }

    public function setStrona(?Kontrahent $strona): self
    {
        $this->strona = $strona;
        if ($this->kierunek)
            $this->UstalStroneNaPodstawieKierunku($this->strona, $this->kierunek);
        return $this;
    }
    public function setSciezkaGenerUrl(string $s)
    {
        $this->sciezkaGenerUrl = $s;
    }
    public function getSciezkaGenerUrl()
    {
        return $this->sciezkaGenerUrl;
    }

    /**
     * @return Collection|WyrazWciagu[]
     */
    public function getOpisCol(): Collection
    {
        return $this->opis;
    }

    public function getOpis(): string
    {
        if ($this->konw == null) $this->konw = new KonwOpis_Str_Acoll;
        return $this->konw->Acoll_to_string($this->opis);
    }

    public function addOpi(WyrazWciagu $opi): self
    {
        if (!$this->opis->contains($opi)) {
            $this->opis[] = $opi;
            $opi->setPismo($this);
        }

        return $this;
    }

    public function removeOpi(WyrazWciagu $opi): self
    {
        if ($this->opis->removeElement($opi)) {
            // set the owning side to null (unless already changed)
            if ($opi->getPismo() === $this) {
                $opi->setPismo(null);
            }
        }

        return $this;
    }
    public function setOpis(?string $opis): Pismo
    {
        if (!$this->konw) $this->konw = new KonwOpis_Str_Acoll;
        $this->opis = $this->konw->String_to_Collection($opis);
        $this->opisCiag = $opis;
        foreach ($this->opis as $o) $o->setPismo($this);
        return $this;
    }
    public function setOpisJesliZmieniony(?string $nowyOpis): bool
    {
        if ($nowyOpis === $this->getOpis())
            return false;
        foreach ($this->opis as $o) $this->niepotrzebneWyrazy[] = $o;
        foreach ($this->niepotrzebneWyrazy as $n) $this->removeOpi($n);
        $this->setOpis($nowyOpis);
        return true;
    }
    public function NiepotrzebneWyrazy()
    {
        return $this->niepotrzebneWyrazy;
    }
    public function PrzechwycOpisyNowychsSpraw(array &$sprawy)
    {
        $doUsuniecia = [];
        foreach ($sprawy as $s) {
            if (!is_numeric($s)) {
                $sprawa = new Sprawa;
                $sprawa->setOpis($s);
                $this->addSprawy(($sprawa));
                $w = $s;
                $doUsuniecia[] = $w;
            }
        }
        $sprawy = array_values(array_diff($sprawy, $doUsuniecia));
    }
    public function UtworzIdodajNoweSprawyWgOpisow(array $opisySpraw)
    {
        foreach ($opisySpraw as $s) {
            $sprawa = new Sprawa;
            $sprawa->setOpis($s);
            $this->addSprawy(($sprawa));
        }
    }
    public function UtworzIustawNowyRodzaj(string $nazwaRodzaju)
    {
        if(!strlen($nazwaRodzaju)) return;
        $rodzaj = new RodzajDokumentu();
        $rodzaj->setNazwa($nazwaRodzaju);
        $this->setRodzaj($rodzaj);
    }

    public function getOpisCiag(): ?string
    {
        return $this->opisCiag;
    }

    public function setOpisCiag(?string $opisCiag): self
    {
        $this->opisCiag = $opisCiag;

        return $this;
    }
    public function OpisZnazwyPliku()
    {
        if (count($this->opis)) {
            $this->opisCiag = $this->getOpis();
            return;
        }
        $poz = strpos($this->nazwaPliku, '.');

        $nazwa = ($poz != false) ? substr($this->nazwaPliku, 0, $poz) : $this->nazwaPliku;
        $nazwa = str_replace("_", " ", $nazwa);
        $this->setOpis($nazwa);
    }
    public function OznaczenieKonwertujDlaUzytkownika(string $oznBazy): string
    {
        $czyFormatBazy = preg_match('|[\d]{4}_[\d]{5}|', $oznBazy);
        if (!$czyFormatBazy) return $oznBazy;
        $arr = explode('_', $oznBazy);
        if (count($arr) != 2) return 'zły format';
        return 'L.dz. ' . ltrim($arr[1], '0') . '/' . $arr[0];
    }
    public function OznaczenieKonwertujDlaBazy(string $ozUzytk): string
    {
        // $ozUzytk = 'litery394znowu';
        $arr = array();
        // preg_match('/L\.dz\. ([\d]+)\/([\d]{4})/',$ozUzytk,$arr);
        preg_match('|([\d]+)\/([\d]{4})|', $ozUzytk, $arr); //wersja zamienna, krótsza
        if (!$arr || count($arr) != 3) return '';
        return $arr[2] . '_' . sprintf('%05s', $arr[1]);
    }
    public function ZwiekszNumeracjeOznaczeniaUzytkownika(string $ozn, int $num = 1): string
    {
        return preg_replace_callback(
            '/([\d]+)(\/)([\d]{4})/',
            function ($matches) use ($num) {

                return strval(intval($matches[1]) + $num) . $matches[2] . $matches[3];
            },
            $ozn
        );
    }
    public function WewnetrznieKonwertujDlaUzytkownikaIzwiekszOjeden()
    {
        $ozn = $this->OznaczenieKonwertujDlaUzytkownika($this->oznaczenie);
        $this->oznaczenie = $this->ZwiekszNumeracjeOznaczeniaUzytkownika($ozn);
    }
    public function NaPodstawieMojegoOznZaproponujOznaczenieZaktualnymRokiem()
    {
        if ($this->oznaczenie === '' || $this->oznaczenie === null) {
            $this->oznaczenie = '2001_00005'; //bez znaczenia
        }
        $ozn = $this->OznaczenieKonwertujDlaUzytkownika($this->oznaczenie);
        return preg_replace_callback(
            '/([\d]+)(\/)([\d]{4})/',
            function ($matches) {
                $data = new DateTime('now');
                $rokTeraz = $data->format('Y');
                // $rokTeraz = intval($data->format('Y'));
                $numer = strval(1 + intval($matches[1]));
                $rok = $matches[3];
                if ($rok != $rokTeraz) {
                    $numer = "1";
                    $rok = $rokTeraz;
                }
                return $numer . $matches[2] . $rok;
            },
            $ozn
        );
    }
    public function getOznaczenieUzytkownika(): string
    {
        $ozn = $this->oznaczenie;
        $czyFormatBazy = preg_match('|[\d]{4}_[\d]{5}|', $ozn);
        $res = $czyFormatBazy ? $this->OznaczenieKonwertujDlaUzytkownika($ozn) : $ozn;
        return ($res == null) ? '' : $res;
    }
    public function getRozmiarDokumentu(): int
    {
        return $this->rozmiar;
    }
    public function setRozmiar(int $rozmiar)
    {
        $this->rozmiar = $rozmiar;
    }
    public function RozmiarCzytelny(): string
    {
        $jednostki = ["B", "kiB", "MiB"];
        $zaDuze = true;
        $podzielone = $this->rozmiar;
        while ($zaDuze) {
            $jednostka = array_shift($jednostki);
            if ($podzielone < 1024) return sprintf('%3.1f', $podzielone) . " " . $jednostka;
            $podzielone = $podzielone / 1024;
        }

        return $this->rozmiar . " B";
    }
    public function RozmiarOkreslPoUstaleniuPolozenia(string $polozeniePliku)
    {
        $plik = $polozeniePliku . $this->nazwaPliku;
        $this->rozmiar = @filesize($plik);
    }
    public function setRouter(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }
    public function UrlWidokNowe()
    {
        if (!isset($this->router)) throw new Exception(
            'należy ustawić router dla pisma'
        );
        return $this->router->generate('pismo_nowe_ze_skanu', [
            'nazwa' => $this->nazwaPliku,
            'numerStrony' => $this->numerStrony
        ]);
    }
    public function setNumerStrony(int $numerStrony)
    {
        $this->numerStrony = $numerStrony;
    }
    public function getNumerStrony()
    {
        return $this->numerStrony;
    }
}
