<?php

namespace App\Entity;

use App\Repository\FolderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FolderRepository::class)
 */
class Folder
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
    private $sciezkaMoja;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSciezkaMoja(): ?string
    {
        return $this->sciezkaMoja;
    }

    public function setSciezkaMoja(string $sciezkaMoja): self
    {
        $this->sciezkaMoja = $sciezkaMoja;

        return $this;
    }
}
