<?php

namespace App\Entity;

use App\Repository\KontrahentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=KontrahentRepository::class)
 */
class Kontrahent
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
     * @ORM\OneToMany(targetEntity=Pismo::class, mappedBy="nadawca")
     */
    private $odeMnie;

    /**
     * @ORM\OneToMany(targetEntity=Pismo::class, mappedBy="odbiorca")
     */
    private $doMnie;

    public function __construct()
    {
        $this->odeMnie = new ArrayCollection();
        $this->doMnie = new ArrayCollection();
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
    public function getOdeMnie(): Collection
    {
        return $this->odeMnie;
    }

    public function addOdeMnie(Pismo $odeMnie): self
    {
        if (!$this->odeMnie->contains($odeMnie)) {
            $this->odeMnie[] = $odeMnie;
            $odeMnie->setNadawca($this);
        }

        return $this;
    }

    public function removeOdeMnie(Pismo $odeMnie): self
    {
        if ($this->odeMnie->removeElement($odeMnie)) {
            // set the owning side to null (unless already changed)
            if ($odeMnie->getNadawca() === $this) {
                $odeMnie->setNadawca(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Pismo[]
     */
    public function getDoMnie(): Collection
    {
        return $this->doMnie;
    }

    public function addDoMnie(Pismo $doMnie): self
    {
        if (!$this->doMnie->contains($doMnie)) {
            $this->doMnie[] = $doMnie;
            $doMnie->setOdbiorca($this);
        }

        return $this;
    }

    public function removeDoMnie(Pismo $doMnie): self
    {
        if ($this->doMnie->removeElement($doMnie)) {
            // set the owning side to null (unless already changed)
            if ($doMnie->getOdbiorca() === $this) {
                $doMnie->setOdbiorca(null);
            }
        }

        return $this;
    }
}
