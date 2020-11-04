<?php

namespace App\Entity;

use App\Repository\PartyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PartyRepository::class)
 */
class Party implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=Card::class)
     */
    private $cards;

    /**
     * @ORM\Column(type="boolean")
     */
    private $cardsHidden;

    /**
     * @ORM\Column(type="string", length=255,unique=true)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $creator;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastTurn;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberOfPlayers;

    /**
     * @ORM\ManyToMany(targetEntity=Player::class, inversedBy="parties")
     */
    private $players;

    /**
     * @ORM\Column(type="boolean")
     */
    private $started;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $turn;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
        $this->players = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Card[]
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): self
    {
        if (!$this->cards->contains($card)) {
            $this->cards[] = $card;
        }

        return $this;
    }

    public function removeCard(Card $card): self
    {
        $this->cards->removeElement($card);

        return $this;
    }

    public function getCardsHidden(): ?bool
    {
        return $this->cardsHidden;
    }

    public function setCardsHidden(bool $cardsHidden): self
    {
        $this->cardsHidden = $cardsHidden;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCreator(): ?Player
    {
        return $this->creator;
    }

    public function setCreator(?Player $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getLastTurn(): ?string
    {
        return $this->lastTurn;
    }

    public function setLastTurn(string $lastTurn): self
    {
        $this->lastTurn = $lastTurn;

        return $this;
    }

    public function getNumberOfPlayers(): ?int
    {
        return $this->numberOfPlayers;
    }

    public function setNumberOfPlayers(int $numberOfPlayers): self
    {
        $this->numberOfPlayers = $numberOfPlayers;

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
        }

        return $this;
    }

    public function removePlayer(Player $player): self
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getStarted(): ?bool
    {
        return $this->started;
    }

    public function setStarted(bool $started): self
    {
        $this->started = $started;

        return $this;
    }

    public function getTurn(): ?string
    {
        return $this->turn;
    }

    public function setTurn(string $turn): self
    {
        $this->turn = $turn;

        return $this;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
