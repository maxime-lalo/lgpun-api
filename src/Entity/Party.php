<?php

namespace App\Entity;

use App\Repository\PartyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use MongoDB\BSON\Persistable;

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
     * @ORM\Column(type="boolean")
     */
    private $cardsHidden;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $creator;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class)
     */
    private $turn;

    /**
     * @ORM\OneToMany(targetEntity=Player::class, mappedBy="party")
     */
    private $players;

    /**
     * @ORM\ManyToMany(targetEntity=Card::class)
     */
    private $cards;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $started;

    /**
     * @ORM\ManyToMany(targetEntity=NotUsedCard::class)
     */
    private $notUsedCards;


    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->cards = new ArrayCollection();
        $this->notUsedCards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function jsonSerialize()
    {
        return get_object_vars($this);
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

    public function getTurn(): ?Player
    {
        return $this->turn;
    }

    public function setTurn(?Player $turn): self
    {
        $this->turn = $turn;

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getPlayers()
    {
        return $this->players;
    }

    public function setPlayers($players){
        $this->players = $players;
    }

    public function addPlayer(Player $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
            $player->setParty($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): self
    {
        if ($this->players->removeElement($player)) {
            // set the owning side to null (unless already changed)
            if ($player->getParty() === $this) {
                $player->setParty(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Card[]
     */
    public function getCards()
    {
        return $this->cards;
    }

    public function setCards($cards){
        $this->cards = $cards;
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

    public function getStarted(): ?bool
    {
        return $this->started;
    }

    public function setStarted(?bool $started): self
    {
        $this->started = $started;

        return $this;
    }

    /**
     * @return Collection|NotUsedCard[]
     */
    public function getNotUsedCards(): Collection
    {
        return $this->notUsedCards;
    }

    public function addNotUsedCard(NotUsedCard $notUsedCard): self
    {
        if (!$this->notUsedCards->contains($notUsedCard)) {
            $this->notUsedCards[] = $notUsedCard;
        }

        return $this;
    }

    public function setNotUsedCards($cards){
        $this->notUsedCards = $cards;
    }

    public function removeNotUsedCard(NotUsedCard $notUsedCard): self
    {
        $this->notUsedCards->removeElement($notUsedCard);

        return $this;
    }
}
