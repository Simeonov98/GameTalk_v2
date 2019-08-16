<?php


namespace AppBundle\Service\Message;


use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Message;

interface MessageServiceInterface
{
    public function create(Message $message,int $recipientId):bool;

    public function getAllByUser();

    public function getOne(int $id): ?Message;

    public function getAllUnseenByUser();
}