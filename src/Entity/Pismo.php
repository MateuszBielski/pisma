<?php

namespace App\Entity;

use App\Repository\PismoRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=PismoRepository::class)
 * @ORM\Table(name="pismo",uniqueConstraints={@ORM\UniqueConstraint(name="nazwa_pliku_unikalna", columns={"nazwa_pliku"})})
 * @UniqueEntity("nazwaPliku",
 *     message="Proszę użyć innej nazwy pliku, ta jest już używana")
 * 
 */
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
    private $nazwaPliku;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $oznaczenie;

    private $adresZrodlaPrzedZarejestrowaniem;
    private $dataModyfikacji;
    private $folderPodgladu = 'png/';
    private $nazwaPlikuPrzedZmiana = '';
    private $sciezkaDoFolderuPdf = "";
    private $sciezkaGenerUrl;

    /**
     * @ORM\ManyToMany(targetEntity=Sprawa::class, mappedBy="dokumenty")
     */
    private $sprawy;

    /**
     * @ORM\ManyToOne(targetEntity=Kontrahent::class, inversedBy="odeMnie")
     */
    private $nadawca;

    /**
     * @ORM\ManyToOne(targetEntity=Kontrahent::class, inversedBy="doMnie")
     */
    private $odbiorca;

    /**
     * @ORM\ManyToOne(targetEntity=RodzajDokumentu::class, inversedBy="dokumenty")
     */
    private $rodzaj;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dataDokumentu = null;

        
    private $kierunek = 1;

   
    private $strona;

    /**
     * @ORM\OneToMany(targetEntity=WyrazWciagu::class, mappedBy="pismo")
     */
    private $opis;
    // private $nazwaZrodlaPrzedZarejestrowaniem;

    public function __construct(string $adresZrodlaPrzedZarejestrowaniem = "")
    {
        $this->adresZrodlaPrzedZarejestrowaniem = $adresZrodlaPrzedZarejestrowaniem;
        // $this->dataModyfikacji = @date("Y-m-d H:i", @filemtime($adresZrodlaPrzedZarejestrowaniem));
        $timestamp = @filemtime($adresZrodlaPrzedZarejestrowaniem);
        $this->dataDokumentu = new DateTime();
        $this->dataDokumentu->setTimestamp($timestamp);
        $this->dataModyfikacji = @date("Y-m-d",$timestamp);
        $this->nazwaPliku = $this->getNazwaZrodlaPrzedZarejestrowaniem();
        $this->sprawy = new ArrayCollection();
        $this->opis = new ArrayCollection();
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
        $this->oznaczenie = $oznaczenie;

        return $this;
    }
    public function getNazwaZrodlaPrzedZarejestrowaniem(): string
    {
        $arr = explode('/',$this->adresZrodlaPrzedZarejestrowaniem);
        return end($arr);
    }
    public function NazwaSkroconaZrodla(int $dlugosc): string
    {
        $skrot = $this->getNazwaZrodlaPrzedZarejestrowaniem();
        $przod = substr($skrot,0,-4);
        // print("\n".strlen($przod)."   ".$dlugosc+1);
        // echo "\nprzod:  ".$przod." strlen: ".strlen($przod);
        if(strlen($przod) < $dlugosc+3)return $skrot;
        
        $przod = substr($przod,0,$dlugosc);
        $tyl = substr($skrot,-4);

        return $przod."..".$tyl;
    }
    public function getAdresZrodlaPrzedZarejestrowaniem()
    {
        return $this->adresZrodlaPrzedZarejestrowaniem;
    }
    public function SciezkaDoPlikuPierwszejStronyDuzegoPodgladuPrzedZarejestrowaniem(): string
    {
        $nazwaBezRozszerzenia = $this->NazwaZrodlaBezRozszerzenia();
        return "/".$this->folderPodgladu.$nazwaBezRozszerzenia."/".$nazwaBezRozszerzenia."-000001.png";
    }
    private function SciezkiPodgladowDlaNazwy(string $nazwa,$slashWiodacy = true): array
    {
        $sciezki = [];
        // $nazwaBezRozszerzenia = $this->NazwaZrodlaBezRozszerzenia();
        $path = $this->folderPodgladu.$nazwa;
        $nazwy = @array_diff(@scandir($path), array('..', '.'));
        if(!$nazwy || !count($nazwy)){
            $sciezki[] = "folder $path jest pusty";
            return $sciezki;
        }
        foreach($nazwy as $n)
        {
            $arr = explode('.',$n);
            $extension = end($arr);

            if('png' == $extension)
            {
                
                $s = $path."/".$n;
                if($slashWiodacy)$s = "/".$s;

                // $s = $path."/".$n;
                $sciezki[] = $s;
                // echo "\n".$s;
            }
        }
        return $sciezki;
    }
    public function SciezkiDoPlikuPodgladowPrzedZarejestrowaniem($slashWiodacy = true): array
    {
       return $this->SciezkiPodgladowDlaNazwy($this->NazwaZrodlaBezRozszerzenia(),$slashWiodacy); 
    }
    public function SciezkiDoPlikuPodgladowDlaNazwyPrzedZmiana($slashWiodacy = true): array
    {
        $nazwaPlikuBezRozszerzenia = substr($this->nazwaPlikuPrzedZmiana,0,strrpos($this->nazwaPlikuPrzedZmiana,'.'));
        // echo "\nXXXx  ".$this->nazwaPliku." yyy ".$nazwaPlikuBezRozszerzenia;
        return $this->SciezkiPodgladowDlaNazwy($nazwaPlikuBezRozszerzenia,$slashWiodacy);
    }
    public function GenerujNazwyZeSciezkamiDlaDocelowychPodgladow(): array
    {
        $ileStronPodgladu = count($this->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem());
        $nazwy = [];
        $nazwaPlikuBezRozszerzenia = substr($this->nazwaPliku,0,strrpos($this->nazwaPliku,'.'));
        for($i = 1 ; $i <= $ileStronPodgladu ; $i++)
        {   
            $nazwy[] = $this->folderPodgladu.$nazwaPlikuBezRozszerzenia."/".$nazwaPlikuBezRozszerzenia."-00000".$i.".png";
        }
        return $nazwy;
    }
    public function GenerujNazwyDocelowychPodgladowZeSciezkamiWfolderzeZrodlowym()
    {
        $ileStronPodgladu = count($this->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem());
        $nazwy = [];
        $nazwaPlikuBezRozszerzenia = substr($this->nazwaPliku,0,strrpos($this->nazwaPliku,'.'));
        $nazwaZrodlaPrzedZarejestrowaniem = $this->NazwaZrodlaBezRozszerzenia();
        for($i = 1 ; $i <= $ileStronPodgladu ; $i++)
        {   
            // $nazwy[] = $this->folderPodgladu.$nazwaZrodlaPrzedZarejestrowaniem."/".$nazwaPlikuBezRozszerzenia."-00000".$i.".png";
            $nazwy[] = $this->folderPodgladu.$nazwaZrodlaPrzedZarejestrowaniem."/".$nazwaPlikuBezRozszerzenia."-".sprintf('%06s', $i).".png";
        }
        return $nazwy;
    }
    public function GenerujNazwyDocelowychPodgladowZeSciezkamiWfolderzePrzedZmiana()
    {
        $ileStronPodgladu = count($this->SciezkiDoPlikuPodgladowDlaNazwyPrzedZmiana());
        $nazwy = [];
        $nazwaBezRozszerzeniaPrzedZmiana = substr($this->nazwaPlikuPrzedZmiana,0,strrpos($this->nazwaPlikuPrzedZmiana,'.'));
        $nazwaBezRozszerzeniaPoZmianie = substr($this->nazwaPliku,0,strrpos($this->nazwaPliku,'.'));
        for($i = 1 ; $i <= $ileStronPodgladu ; $i++)
        {   
            // $nazwy[] = $this->folderPodgladu.$nazwaZrodlaPrzedZarejestrowaniem."/".$nazwaPlikuBezRozszerzenia."-00000".$i.".png";
            $nazwy[] = $this->folderPodgladu.$nazwaBezRozszerzeniaPrzedZmiana."/".$nazwaBezRozszerzeniaPoZmianie."-".sprintf('%06s', $i).".png";
        }
        return $nazwy;

    }
    public function SciezkiDoPlikuPodgladowZarejestrowanych(): array
    {
        $nazwaPlikuBezRozszerzenia = substr($this->nazwaPliku,0,strrpos($this->nazwaPliku,'.'));
        // echo "\nXXXx  ".$this->nazwaPliku." yyy ".$nazwaPlikuBezRozszerzenia;
        return $this->SciezkiPodgladowDlaNazwy($nazwaPlikuBezRozszerzenia);
    }
    
    public function FolderZpodlgademPngWzglednieZgodnieZeZrodlem()
    {
        return $this->folderPodgladu.$this->NazwaZrodlaBezRozszerzenia()."/";
    }
    public function FolderZpodlgademPngWzglednieZgodnieZnazwaPliku()
    {
        // $arr = explode('.',$this->nazwaPliku);

        $nazwaBezRozszerzenia =  substr($this->nazwaPliku,0,strrpos($this->nazwaPliku,'.'));
        return $this->folderPodgladu.$nazwaBezRozszerzenia."/";
    }
    public function FolderZpodlgademPngWzglednieZgodnieZnazwaPrzedZmiana()
    {
        $nazwaBezRozszerzenia =  substr($this->nazwaPlikuPrzedZmiana,0,strrpos($this->nazwaPlikuPrzedZmiana,'.'));
        return $this->folderPodgladu.$nazwaBezRozszerzenia."/";
    }
    public function NazwaZrodlaBezRozszerzenia(): string
    {
        $nazwa = $this->getNazwaZrodlaPrzedZarejestrowaniem();
        return substr($nazwa,0,strrpos($nazwa,'.'));
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
            $sprawy->addDokumenty($this);
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
        if(!$nadawca)return $this;
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
        if(!$odbiorca)return $this;
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
        if($this->dataDokumentu != null)return false;
        $this->dataDokumentu = new DateTime();
        $this->dataDokumentu->setTimestamp(@filemtime($this->sciezkaDoFolderuPdf.$this->nazwaPliku));
        return true;
    }

    
    public function UstalStroneNaPodstawieKierunku(?Kontrahent $strona, int $kierunek)
    {
        if($kierunek == 1)
        {
            $this->nadawca = $strona;
            $this->odbiorca = null;
        }
        if($kierunek == 2)
        {
            $this->nadawca = null;
            $this->odbiorca = $strona;
        }
    }
    public function UstalStroneIKierunek()
    {
        if($this->nadawca)
        {
            $this->strona = $this->nadawca;
            $this->kierunek = 1;
            return;
        }
        if($this->odbiorca)
        {
            $this->strona = $this->odbiorca;
            $this->kierunek = 2;
            return;
        }
    }

    public function getKierunek(): ?int
    {
        return $this->kierunek;
    }
    public function getKierunekOpisowo()
    {
        switch($this->kierunek)
        {
            case 1:
            return 'przychodzące od: ';
            case 2:
            return 'wychodzące do: ';

        }
    }

    public function setKierunek(?int $kierunek): self
    {
        $this->kierunek = $kierunek;
        if($this->strona)
        $this->UstalStroneNaPodstawieKierunku($this->strona,$kierunek);
        return $this;
    }

    public function getStrona(): ?Kontrahent
    {
        return $this->strona;
    }

    public function setStrona(?Kontrahent $strona): self
    {
        $this->strona = $strona;
        if($this->kierunek)
        $this->UstalStroneNaPodstawieKierunku($this->strona,$this->kierunek);
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
    public function getOpis(): Collection
    {
        return $this->opis;
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
}
