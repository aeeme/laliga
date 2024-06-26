<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="players")
 */

class Player
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nombre;

    /**
     * @ORM\Column(type="float")
     */
    private $salario;

    /**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="players")
     * @ORM\JoinColumn(name=club_id, referencedColumnName="id", onDelete="SET NULL")
     */
    private $team;

    public function getId(): ?int{
        return $this->id;
    }

    public function getName(): ?string{
        return $this->nombre;
    }

    public function setName(string $nombre): self{
        $this->nombre = $nombre;
        return $this;
    }

    public function getSalario(): ?int{
        return $this->salario;
    }

    public function setSalario(int $salario): self{
        $this->salario = $salario;
        return $this;
    }

    public function getTeam(): ?Team{
        return $this->team;
    }

    public function setTeam(?Team $team): self{
        $this->team = $team;
        return $this;
    }
}