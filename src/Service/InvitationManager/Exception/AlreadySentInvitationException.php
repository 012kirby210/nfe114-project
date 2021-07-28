<?php


namespace App\Service\InvitationManager\Exception;


class AlreadySentInvitationException extends \Exception
{
    public function  __construct(string $message)
    {
        parent::__construct($message);
    }
}