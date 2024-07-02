<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'Coaches')]
class Coach
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;


    #[ORM\Column(type: 'string', length: 45)]
    private $nombre;


    #[ORM\Column(type: 'float')]
    private $salario;


    #[ORM\OneToOne(targetEntity: 'Team', inversedBy: 'coaches')]
    #[ORM\JoinColumn(name: 'club_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $team;


    #[ORM\Column(type: 'string', length: 100)]
    private $email;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getSalario(): ?float
    {
        return $this->salario;
    }

    public function setSalario(String $salario): self
    {
        $this->salario = $salario;
        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;
        return $this;
    }

    public function getEmail(): ?string{
        return $this->email;
    }

    public function setEmail(string $email): self{
        $this->email = $email;
        return $this;
    }
}