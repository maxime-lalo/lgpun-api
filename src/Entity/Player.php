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
     * @ORM\ManyToOne(targetEntity=Party::class, inversedBy="players")
     */
    private $party;

    public function __construct()
    {
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

    public function jsonSerialize()
    {
        return [
            "id" => $this->id,
            "id_firebase" => $this->id_firebase,
            "pseudo" => $this->pseudo,
            "beginning_card" => $this->beginningCard,
            "ending_card" => $this->getEndingCard()
        ];
    }

    public function getParty(): ?Party
    {
        return $this->party;
    }

    public function setParty(?Party $party): self
    {
        $this->party = $party;

        return $this;
    }
}
