<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
#[ORM\Table(name: "Teams")]

class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
//    #[Assert\Length(max: 100)]
//    #[Assert\NotBlank]
    private string $nombre;


    #[ORM\Column(type: 'float')]
//    #[Assert\NotBlank]
//    #[Assert\Type(type: 'numeric', message: 'El presupuesto debe tener un valor numérico.')]
//    #[Assert\GreaterThanOrEqual(value:0, message:'El presupuesto debe ser mayor o igual a 0.')]
    private int $presupuesto_actual;

    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'team')]
    private $players;

    #[ORM\OneToOne(targetEntity: Coach::class, mappedBy: 'team')]
    private $coach;

//    public function __construct()
//    {
//        $this->players = new ArrayCollection();
//    }

    public function getPlayers()
    {
        return $this->players;
    }

    public function addPlayer(Player $player)
    {
        $player->setTeam($this);
        $this->players->add($player);
    }

    public function removePlayer(Player $player)
    {
        if ($this->players->removeElement($player)) {
            if ($player->getTeam() === $this) {
                $player->setTeam(null);
            }
        }
        return $this;
    }

    public function getCoach(): ?coach
    {
        return $this->coach;
    }

    public function setCoach(?coach $coach): void
    {
        if ($coach !== null && $this->coach !==null) {
            throw new \LogicException('This club already has a coach associated with it.');
        }
        $this->coach = $coach;
        if ($coach !== null) {
            $coach->setTeam($this);
        }
    }

    public function removeCoach(Coach $coach): self
    {
        if ($this->coach->removeCoach($coach)) {
            if ($coach->getTeam() === $this) {
                $coach->setTeam(null);
            }
        }
        return $this;
    }

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

    public function getPresupuestoActual(): ?float
    {
        return $this->presupuesto_actual;
    }

    public function setPresupuestoActual(float $presupuesto_actual): self
    {
        $this->presupuesto_actual = $presupuesto_actual;
        return $this;
    }
}