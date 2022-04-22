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
    public function SciezkaTuJestem()
    {
        $tuJestem = [['folder' => "/", 'sciezka' => "+"]];
        $foldery = explode("/",$this->sciezkaMoja);
        $ostatni = count($foldery) - 1;
        $sciezka = "";
        for($i = 1 ; $i < $ostatni; $i++)
        {
            $sciezka .="+".str_replace('+','++',$foldery[$i]);
            $tuJestem[] = ['folder' => $foldery[$i]."/", 'sciezka' => $sciezka];
        }
        $sciezka .="+".str_replace('+','++',$foldery[$ostatni]);
        $tuJestem[] = ['folder' => $foldery[$ostatni], 'sciezka' => $sciezka];
        return $tuJestem;
    }
    public function SciezkePobierzZadresuIkonwertuj(string $sciezkaZadresu)
    {
        $sciezkaZadresu = str_replace("+","/",$sciezkaZadresu);
        $this->sciezkaMoja = str_replace("//","+",$sciezkaZadresu);
    }
}
