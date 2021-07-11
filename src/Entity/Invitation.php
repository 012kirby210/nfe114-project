<?php

namespace App\Entity;

use App\Repository\InvitationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InvitationRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Invitation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=profile::class, inversedBy="sentInvitations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $host;

    /**
     * @ORM\ManyToOne(targetEntity=profile::class, inversedBy="receivedInvitations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $guest;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $create_datetime;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $update_datetime;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $commentaires;

    /**
     * @ORM\ManyToOne (targetEntity=Conversation::class, inversedBy="invitations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $conversation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHost(): ?profile
    {
        return $this->host;
    }

    public function setHost(?profile $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getGuest(): ?profile
    {
        return $this->guest;
    }

    public function setGuest(?profile $guest): self
    {
        $this->guest = $guest;

        return $this;
    }

    /**
     * @return Collection|Conversation[]
     */
    public function getConversation(): Collection
    {
        return $this->conversation;
    }

    public function addConversation(Conversation $conversation): self
    {
        if (!$this->conversation->contains($conversation)) {
            $this->conversation[] = $conversation;
            $conversation->setRelatedInvitations($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): self
    {
        if ($this->conversation->removeElement($conversation)) {
            // set the owning side to null (unless already changed)
            if ($conversation->getRelatedInvitations() === $this) {
                $conversation->setRelatedInvitations(null);
            }
        }

        return $this;
    }

    public function getCreateDatetime(): ?string
    {
        return $this->create_datetime;
    }

    public function setCreateDatetime(?string $create_datetime): self
    {
        $this->create_datetime = $create_datetime;

        return $this;
    }

    public function getUpdateDatetime(): ?string
    {
        return $this->update_datetime;
    }

    public function setUpdateDatetime(string $update_datetime): self
    {
        $this->update_datetime = $update_datetime;

        return $this;
    }

    public function getCommentaires(): ?string
    {
        return $this->commentaires;
    }

    public function setCommentaires(?string $commentaires): self
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    public function getConversations(): ?Conversation
    {
        return $this->conversations;
    }

    public function setConversations(?Conversation $conversations): self
    {
        $this->conversations = $conversations;

        return $this;
    }
}
