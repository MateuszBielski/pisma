<?php

namespace App\Entity;

use App\Repository\PismoRepository;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * @ORM\Entity(repositoryClass=PismoRepository::class)
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
    public function SciezkiDoPlikuPodgladowPrzedZarejestrowaniem(): array
    {
        $sciezki = [];
        $nazwaBezRozszerzenia = $this->NazwaZrodlaBezRozszerzenia();
        $path = $this->folderPodgladu.$nazwaBezRozszerzenia;
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
                $s = "/".$path."/".$n;
                $sciezki[] = $s;
                // echo "\n".$s;
            }
        }
        return $sciezki;
    }
    public function FolderZpodlgademPngWzglednie()
    {
        return $this->folderPodgladu.$this->NazwaZrodlaBezRozszerzenia()."/";
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
    public function JestPodglad(): bool
    {
        return file_exists($this->FolderZpodlgademPngWzglednie());
        // return false;
    }
    public function setFolderPodgladu(string $path)
    {
        $this->folderPodgladu = $path;
    }
}
