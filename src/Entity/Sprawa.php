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
     * @ORM\Column(type="string", length=255)
     */
    private $nazwa;

    /**
     * @ORM\ManyToMany(targetEntity=Pismo::class, inversedBy="sprawy")
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
        }

        return $this;
    }

    public function removeDokumenty(Pismo $dokumenty): self
    {
        $this->dokumenty->removeElement($dokumenty);

        return $this;
    }
}
