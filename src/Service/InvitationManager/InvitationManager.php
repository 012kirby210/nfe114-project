<?php


namespace App\Service\InvitationManager;


use App\Entity\Profile;
use App\Entity\Conversation;
use App\Service\InvitationManager\Exception\AlreadySentInvitationException;
use App\Service\InvitationManager\Exception\NotOwnedConversationException;
use App\Service\InvitationManager\InvitationManagerInterface\InvitationManagerInterface;
use App\Service\InvitationManager\InvitationManagerInterface\InvitationSenderInterface;

class InvitationManager implements InvitationManagerInterface
{
    private $sender;

    public function __construct(InvitationSenderInterface $sender)
    {
        $this->sender = $sender;
    }

    public function send(Profile $host,Profile $guest,Conversation $conversation)
    {
        $this->sender->send($host,$guest,$conversation);
    }
}