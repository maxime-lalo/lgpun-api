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


    public function __construct()
    {
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
}
