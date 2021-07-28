<?php


namespace App\Service\InvitationManager\Exception;


class SelfReferencingInvitationException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

}