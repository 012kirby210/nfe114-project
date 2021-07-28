<?php


namespace App\Service\InvitationManager;


use App\Entity\Conversation;
use App\Entity\Invitation;
use App\Entity\Profile;
use App\Service\InvitationManager\Exception\AlreadySentInvitationException;
use App\Service\InvitationManager\Exception\NotOwnedConversationException;
use App\Service\InvitationManager\Exception\SelfReferencingInvitationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class InvitationSenderBaseDecorator implements InvitationManagerInterface\InvitationSenderinterface
{

    private $em;
    private $lucLogger;

    public function __construct(EntityManagerInterface $em,LoggerInterface $lucLogger)
    {
        $this->em = $em;
        $this->lucLogger = $lucLogger;
    }

    public function send(Profile $host, Profile $guest, Conversation $conversation)
    {
        if (!$host->getOwnedConversations()->contains($conversation)){
            throw new NotOwnedConversationException('cannot invite on a conversation not owned.');
        }
        // we need to check auto reference before the already sent check
        // because of the doctrine persist behaviour.
        if ($host===$guest){
            throw new SelfReferencingInvitationException('invitation auto référencée.');
        }

        $invitation = new Invitation();
        $invitation
            ->setGuest($guest)->setHost($host)->setConversation($conversation)
            ->setEtat('pending')->setUpdateDatetime(date('Y-m-d H:i:s',time()))
            ->setCreateDatetime(date('Y-m-d H:i:s',time()));

        if ($host->hasAlreadySentTheInvitation($invitation)){
            throw new AlreadySentInvitationException('is already invited.');
        }

        $this->em->persist($invitation);
        $host->addSentInvitation($invitation);
        $this->em->persist($host);
        $this->em->flush();
    }
}