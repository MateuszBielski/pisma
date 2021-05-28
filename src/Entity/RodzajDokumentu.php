<?php

namespace App\Entity;

use App\Repository\RodzajDokumentuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RodzajDokumentuRepository::class)
 */
class RodzajDokumentu
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nazwa;

    /**
     * @ORM\OneToMany(targetEntity=Pismo::class, mappedBy="rodzaj")
     */
    private $dokumenty;

    public function __construct()
    {
        $this->dokumenty = new ArrayCollection();
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
            $dokumenty->setRodzaj($this);
        }

        return $this;
    }

    public function removeDokumenty(Pismo $dokumenty): self
    {
        if ($this->dokumenty->removeElement($dokumenty)) {
            // set the owning side to null (unless already changed)
            if ($dokumenty->getRodzaj() === $this) {
                $dokumenty->setRodzaj(null);
            }
        }

        return $this;
    }
}
