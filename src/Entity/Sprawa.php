<?php

namespace App\Entity;

use App\Repository\SprawaRepository;
use App\Service\KonwOpis_Str_Acoll;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SprawaRepository::class)
 */
class Sprawa
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
    private $nazwa;

    /**
     * @ORM\ManyToMany(targetEntity=Pismo::class, mappedBy="sprawy")
     */
    private $dokumenty;

    /**
     * @ORM\OneToMany(targetEntity=WyrazWciagu::class, mappedBy="sprawa", cascade={"persist", "remove"})
     */
    private $opis;

    private $konw;
    private $niepotrzebneWyrazy = [];

    public function __construct()
    {
        $this->konw = new KonwOpis_Str_Acoll;
        $this->dokumenty = new ArrayCollection();
        $this->opis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNazwa(): ?string
    {
        return $this->nazwa;
    }

    public function setNazwa(string $nazwa): self
    {
        $this->nazwa = $nazwa;

        return $this;
    }

    /**
     * @return Collection|Pismo[]
     */
    public function getDokumenty(): Collection
    {
        return $this->dokumenty;
    }

    public function addDokumenty(Pismo $dokumenty): self
    {
        if (!$this->dokumenty->contains($dokumenty)) {
            $this->dokumenty[] = $dokumenty;
            $dokumenty->addSprawy($this);
        }

        return $this;
    }

    public function removeDokumenty(Pismo $dokumenty): self
    {
        $this->dokumenty->removeElement($dokumenty);

        return $this;
    }

    /**
     * @return Collection|WyrazWciagu[]
     */
    public function getOpisCol(): Collection
    {
        return $this->opis;
    }
    
    //to jest używane przy zapisie
    public function addOpi(WyrazWciagu $opi): self
    {
        // echo count($this->opis)." ";
        if (!$this->opis->contains($opi)) {
            $this->opis[] = $opi;
            $opi->setSprawa($this);
        }

        return $this;
    }

    public function removeOpi(WyrazWciagu $opi): self
    {
        if ($this->opis->removeElement($opi)) {
            if ($opi->getSprawa() === $this) {
                $opi->setSprawa(null);
            }
        }

        return $this;
    }
    public function getOpis(): string
    {
        if($this->konw == null)$this->konw = new KonwOpis_Str_Acoll;
        return $this->konw->Acoll_to_string($this->opis);
    }
    public function setOpis(?string $opis): Sprawa
    {
        $this->opis = $this->konw->String_to_Collection($opis);
        $this->nazwa = $opis;
        foreach($this->opis as $o)$o->setSprawa($this);
        return $this;
    }
    public function setOpisJesliZmieniony(?string $nowyOpis): bool
    {
        if($nowyOpis === $this->getOpis())
        return false;
        foreach($this->opis as $o)$this->niepotrzebneWyrazy[] = $o;
        foreach($this->niepotrzebneWyrazy as $n)$this->removeOpi($n);
        $this->setOpis($nowyOpis);
        return true;
    }
    public function NiepotrzebneWyrazy()
    {
        return $this->niepotrzebneWyrazy;
    }
    public function NazwePobierzZopisu()
    {
        $this->nazwa = $this->getOpis();
    }

}
