<?php

namespace App\Entity;

use App\Repository\PismoRepository;
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
    // private $nazwaZrodlaPrzedZarejestrowaniem;

    public function __construct(string $adresZrodlaPrzedZarejestrowaniem = "")
    {
        $this->adresZrodlaPrzedZarejestrowaniem = $adresZrodlaPrzedZarejestrowaniem;
        $this->dataModyfikacji = @date("Y-m-d H:i", @filemtime($adresZrodlaPrzedZarejestrowaniem));
        $this->nazwaPliku = $this->getNazwaZrodlaPrzedZarejestrowaniem();
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
        $this->nazwaPliku = $nazwaPliku;

        return $this;
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
        $nazwy = array_diff(scandir($path), array('..', '.'));
        if(!count($nazwy)){
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
}
