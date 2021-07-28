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
     * @ORM\ManyToOne(targetEntity=Profile::class, inversedBy="sentInvitations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $host;

    /**
     * @ORM\ManyToOne(targetEntity=Profile::class, inversedBy="receivedInvitations")
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
     * @ORM\ManyToOne (targetEntity=Conversation::class, inversedBy="invitations",)
     * @ORM\JoinColumn(name="conversation_id",referencedColumnName="id",nullable=false)
     */
    private $conversation;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $etat;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHost(): ?Profile
    {
        return $this->host;
    }

    public function setHost(?Profile $host): self
    {
        $this->host = $host;
        if (!$host->getSentInvitations()->contains($this)){
            $host->addSentInvitation($this);
        }
        
        return $this;
    }

    public function getGuest(): ?Profile
    {
        return $this->guest;
    }

    public function setGuest(?Profile $guest): self
    {
        $this->guest = $guest;
        if ($guest->getReceivedInvitations()->contains($this)){
            $guest->addReceivedInvitation($this);
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

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): self
    {
        $this->conversation = $conversation;
        if (!$conversation->getInvitations()->contains($this)){
            $conversation->addInvitation($this);
        }

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

}
