<?php

namespace App\Entity;

use App\Repository\SprawaRepository;
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
     * @ORM\ManyToMany(targetEntity=Pismo::class, inversedBy="sprawy")
     */
    private $dokumenty;

    /**
     * @ORM\OneToMany(targetEntity=WyrazWciagu::class, mappedBy="sprawa", cascade={"persist", "remove"})
     */
    private $opis;

    public function __construct()
    {
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
        if (!$this->opis->contains($opi)) {
            $this->opis[] = $opi;
            $opi->setSprawa($this);
        }

        return $this;
    }

    public function removeOpi(WyrazWciagu $opi): self
    {
        if ($this->opis->removeElement($opi)) {
            // set the owning side to null (unless already changed)
            if ($opi->getSprawa() === $this) {
                $opi->setSprawa(null);
            }
        }

        return $this;
    }
    //używane przy odczycie z bazy
    public function getOpis(): string
    {
        $result = '';
        foreach($this->opis as $wyraz)
        {
            $result .=$wyraz->getWartosc()."+";
        }
        return rtrim($result," ");
    }
    //to chyba nie jest używane
    public function setOpis(?string $opis)
    {
        $this->opis = new ArrayCollection();
        $arr = explode(" ",$opis);
        foreach($arr as $wyraz)
        $this->opis[] = $wyraz;
    }
}
