<?php

namespace App\Entity;

use App\Repository\ProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProfileRepository::class)
 */
class Profile
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="profile", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Type(type="App\Entity\User")
     * @Assert\Valid
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="boolean")
     */
    private $notificationEmail;

    /**
     * @ORM\Column(type="boolean")
     */
    private $notificationDesktop;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $picture;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\OneToMany(targetEntity=Conversation::class, mappedBy="proprietaire")
     */
    private $ownedConversations;

    /**
     * @ORM\OneToMany(targetEntity=Invitation::class, mappedBy="host")
     */
    private $sentInvitations;

    /**
     * @ORM\OneToMany(targetEntity=Invitation::class, mappedBy="guest")
     */
    private $receivedInvitations;

    /**
     * @ORM\ManyToMany(targetEntity=Conversation::class, mappedBy="participants")
     */
    private $participatingConversations;

    public function __construct()
    {
        $this->ownedConversations = new ArrayCollection();
        $this->sentInvitations = new ArrayCollection();
        $this->receivedInvitations = new ArrayCollection();
        $this->participatingConversations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getNotificationEmail(): ?bool
    {
        return $this->notificationEmail;
    }

    public function setNotificationEmail(bool $notificationEmail): self
    {
        $this->notificationEmail = $notificationEmail;

        return $this;
    }

    public function getNotificationDesktop(): ?bool
    {
        return $this->notificationDesktop;
    }

    public function setNotificationDesktop(bool $notificationDesktop): self
    {
        $this->notificationDesktop = $notificationDesktop;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return Collection|Conversation[]
     */
    public function getOwnedConversations(): Collection
    {
        return $this->ownedConversations;
    }

    public function addOwnedConversation(Conversation $conversation): self
    {
        if (!$this->ownedConversations->contains($conversation)) {
            $this->ownedConversations[] = $conversation;
            $conversation->setProprietaire($this);
        }
        if (!$this->participatingConversations->contains($conversation)){
            $this->participatingConversations[] = $conversation;
            $conversation->addParticipant($this);
        }

        return $this;
    }

    /**
     * @return Collection|Invitation[]
     */
    public function getSentInvitations(): Collection
    {
        return $this->sentInvitations;
    }

    public function addSentInvitation(Invitation $sentInvitation): self
    {
        if (!$this->sentInvitations->contains($sentInvitation)) {
            $this->sentInvitations[] = $sentInvitation;
            $sentInvitation->setHost($this);
        }

        return $this;
    }

    public function removeSentInvitation(Invitation $sentInvitation): self
    {
        if ($this->sentInvitations->removeElement($sentInvitation)) {
            // set the owning side to null (unless already changed)
            if ($sentInvitation->getHost() === $this) {
                $sentInvitation->setHost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Invitation[]
     */
    public function getReceivedInvitations(): Collection
    {
        return $this->receivedInvitations;
    }

    public function addReceivedInvitation(Invitation $receivedInvitation): self
    {
        if (!$this->receivedInvitations->contains($receivedInvitation)) {
            $this->receivedInvitations[] = $receivedInvitation;
            $receivedInvitation->setGuest($this);
        }

        return $this;
    }

    public function removeReceivedInvitation(Invitation $receivedInvitation): self
    {
        if ($this->receivedInvitations->removeElement($receivedInvitation)) {
            // set the owning side to null (unless already changed)
            if ($receivedInvitation->getGuest() === $this) {
                $receivedInvitation->setGuest(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Conversation[]
     */
    public function getParticipatingConversations(): Collection
    {
        return $this->participatingConversations;
    }

    public function addParticipatingConversation(Conversation $participatingConversation): self
    {
        if (!$this->participatingConversations->contains($participatingConversation)) {
            $this->participatingConversations[] = $participatingConversation;
            $participatingConversation->addParticipant($this);
        }

        return $this;
    }

    public function removeParticipatingConversation(Conversation $participatingConversation): self
    {
        if ($this->participatingConversations->removeElement($participatingConversation)) {
            $participatingConversation->removeParticipant($this);
        }

        return $this;
    }

    public function hasAlreadySentTheInvitation(Invitation $invitation):bool
    {
        $alreadySentInvitation = false;
        $sentInvitations = $this->getSentInvitations();
        foreach ($sentInvitations as $sentInvitation){
            $alreadySentInvitation = (
                ($sentInvitation->getHost() === $invitation->getHost()) &&
                ($sentInvitation->getGuest() === $invitation->getGuest()) &&
                ($sentInvitation->getEtat() === 'pending')
            );
            if ($alreadySentInvitation) break;
        }
        return $alreadySentInvitation;
    }

}
