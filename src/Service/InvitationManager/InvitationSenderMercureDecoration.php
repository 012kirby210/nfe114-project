<?php

namespace App\Service\InvitationManager;

use App\Entity\Conversation;
use App\Entity\Profile;
use App\Service\InvitationManager\Exception\NotOwnedConversationException;
use App\Service\InvitationManager\InvitationSenderBaseDecorator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use function PHPUnit\Framework\throwException;

class InvitationSenderMercureDecoration implements InvitationManagerInterface\InvitationSenderInterface
{

    private $decorated;
    private $hubInterface;
    private $lucLogger;

    /**
     * !TODO : check if the framework do cycling dependancies if we put an interface
     * instead of the Class
     */
    public function __construct(InvitationSenderBaseDecorator $baseDecorator,
                                HubInterface $hubInterface, LoggerInterface $lucLogger)
    {
        $this->decorated = $baseDecorator;
        $this->hubInterface = $hubInterface;
        $this->lucLogger = $lucLogger;
    }

    public function send(Profile $host, Profile $guest, Conversation $conversation)
    {
        $this->lucLogger->info("entering into the deocrating service");
        $this->decorated->send($host,$guest,$conversation);
        $update = new Update('https://localhost/profile/'.$guest->getUser()->getId().'/invitations',json_encode(['event'=>'nouvelle invitation!']));
        $this->hubInterface->publish($update);
    }
}