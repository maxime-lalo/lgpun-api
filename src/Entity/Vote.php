<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VoteRepository::class)
 */
class Vote implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $target;

    /**
     * @ORM\ManyToMany(targetEntity=Party::class, inversedBy="votes")
     */
    private $party;

    /**
     * @ORM\OneToOne(targetEntity=Player::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct()
    {
        $this->party = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTarget(): ?Player
    {
        return $this->target;
    }

    public function setTarget(?Player $target): self
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return Collection|Party[]
     */
    public function getParty(): Collection
    {
        return $this->party;
    }

    public function addParty(Party $party): self
    {
        if (!$this->party->contains($party)) {
            $this->party[] = $party;
        }

        return $this;
    }

    public function removeParty(Party $party): self
    {
        $this->party->removeElement($party);

        return $this;
    }

    public function getUser(): ?Player
    {
        return $this->user;
    }

    public function setUser(Player $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
