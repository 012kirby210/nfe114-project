<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\ManyToOne(targetEntity=Profile::class, inversedBy="ownedConversations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $proprietaire;

    /**
     * @ORM\Column(type="string", length=255,name="create_datetime")
     */
    private $createDatetime;

    /**
     * @ORM\Column(type="boolean")
     */
    private $archived;

    /**
     * @ORM\OneToMany(targetEntity=Invitation::class, mappedBy="conversation")
     */
    private $invitations;

    /**
     * @ORM\ManyToMany(targetEntity=Profile::class, inversedBy="participatingConversations")
     */
    private $participants;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $updateDateTime;

    public function __construct()
    {
        $this->invitations = new ArrayCollection();
        $this->participants = new ArrayCollection();
    }

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

    public function getProprietaire(): ?Profile
    {
        return $this->proprietaire;
    }

    public function setProprietaire(?Profile $proprietaire): self
    {
        $this->proprietaire = $proprietaire;
        if(!$proprietaire->getOwnedConversations()->contains($this)){
            $proprietaire->addOwnedConversation($this);
        }
        return $this;
    }

    public function getCreateDatetime(): ?string
    {
        return $this->createDatetime;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreateDatetime()
    {
        $this->createDatetime = date('Y-m-d H:i:s',time());
    }

    public function getArchived(): ?bool
    {
        return $this->archived;
    }

    /**
     * @param bool $archived
     * @return $this
     */
    public function setArchived(bool $archived = false): self
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * @return Collection|Invitation[]
     */
    public function getInvitations(): Collection
    {
        return $this->invitations;
    }

    public function addInvitation(Invitation $invitation): self
    {
        if (!$this->invitations->contains($invitation)) {
            $this->invitations[] = $invitation;
            $invitation->setConversation($this);
        }

        return $this;
    }

    public function removeInvitation(Invitation $invitation): self
    {
        if ($this->invitations->removeElement($invitation)) {
            // set the owning side to null (unless already changed)
            if ($invitation->getConversations() === $this) {
                $invitation->setConversations(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Profile[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Profile $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(Profile $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getUpdateDateTime(): ?string
    {
        return $this->updateDateTime;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdateDateTime()
    {
        $this->updateDateTime = date('Y-m-d H:i:s',time());
    }
}
