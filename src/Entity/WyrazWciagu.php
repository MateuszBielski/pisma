<?php

namespace App\Entity;

use App\Repository\WyrazWciaguRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=WyrazWciaguRepository::class)
 */
class WyrazWciagu
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
    private $wartosc;

    /**
     * @ORM\Column(type="integer")
     */
    private $kolejnosc;

    /**
     * @ORM\ManyToOne(targetEntity=Sprawa::class, inversedBy="opis")
     */
    private $sprawa;

    /**
     * @ORM\ManyToOne(targetEntity=Pismo::class, inversedBy="opis")
     */
    private $pismo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWartosc(): ?string
    {
        return $this->wartosc;
    }

    public function setWartosc(string $wartosc): self
    {
        $this->wartosc = $wartosc;

        return $this;
    }

    public function getKolejnosc(): ?int
    {
        return $this->kolejnosc;
    }

    public function setKolejnosc(int $kolejnosc): self
    {
        $this->kolejnosc = $kolejnosc;

        return $this;
    }

    public function getSprawa(): ?Sprawa
    {
        return $this->sprawa;
    }

    public function setSprawa(?Sprawa $sprawa): self
    {
        $this->sprawa = $sprawa;

        return $this;
    }

    public function getPismo(): ?Pismo
    {
        return $this->pismo;
    }

    public function setPismo(?Pismo $pismo): self
    {
        $this->pismo = $pismo;

        return $this;
    }
}
