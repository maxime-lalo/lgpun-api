<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlayerRepository::class)
 */
class Player implements \JsonSerializable
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
    private $id_firebase;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pseudo;

    /**
     * @ORM\ManyToOne(targetEntity=Card::class)
     */
    private $beginningCard;

    /**
     * @ORM\ManyToOne(targetEntity=Card::class)
     */
    private $endingCard;

    /**
     * @ORM\ManyToMany(targetEntity=Party::class, mappedBy="players")
     */
    private $parties;

    public function __construct()
    {
        $this->parties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdFirebase(): ?string
    {
        return $this->id_firebase;
    }

    public function setIdFirebase(string $id_firebase): self
    {
        $this->id_firebase = $id_firebase;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getBeginningCard(): ?Card
    {
        return $this->beginningCard;
    }

    public function setBeginningCard(?Card $beginningCard): self
    {
        $this->beginningCard = $beginningCard;

        return $this;
    }

    public function getEndingCard(): ?Card
    {
        return $this->endingCard;
    }

    public function setEndingCard(?Card $endingCard): self
    {
        $this->endingCard = $endingCard;

        return $this;
    }

    /**
     * @return Collection|Party[]
     */
    public function getParties(): Collection
    {
        return $this->parties;
    }

    public function addParty(Party $party): self
    {
        if (!$this->parties->contains($party)) {
            $this->parties[] = $party;
            $party->addPlayer($this);
        }

        return $this;
    }

    public function removeParty(Party $party): self
    {
        if ($this->parties->removeElement($party)) {
            $party->removePlayer($this);
        }

        return $this;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
