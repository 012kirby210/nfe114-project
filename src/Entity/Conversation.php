<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConversationRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Conversation
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
    private $titre;

    /**
     * @ORM\ManyToOne(targetEntity=Profile::class, inversedBy="conversations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $proprietaire;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $create_datetime;

    /**
     * @ORM\Column(type="boolean")
     */
    private $archived;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getProprietaire(): ?profile
    {
        return $this->proprietaire;
    }

    public function setProprietaire(?profile $proprietaire): self
    {
        $this->proprietaire = $proprietaire;

        return $this;
    }

    public function getCreateDatetime(): ?string
    {
        return $this->create_datetime;
    }

    /**
     * @param string $create_datetime
     * @return $this
     * @ORM\PrePersist
     */
    protected function setCreateDatetime(string $create_datetime): self
    {
        $this->create_datetime = date('Y-m-d H:i:s',time());

        return $this;
    }

    public function getArchived(): ?bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): self
    {
        $this->archived = $archived;

        return $this;
    }
}
