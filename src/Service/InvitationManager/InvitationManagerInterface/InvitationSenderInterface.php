<?php


namespace App\Service\InvitationManager\InvitationManagerInterface;


use App\Entity\Conversation;
use App\Entity\Profile;

interface InvitationSenderInterface
{
    public function send(Profile $host, Profile $guest, Conversation $conversation);
}