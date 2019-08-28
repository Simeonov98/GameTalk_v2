<?php

namespace AppBundle\Repository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use AppBundle\Entity\Message;

/**
 * MessageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MessageRepository extends \Doctrine\ORM\EntityRepository
{
    public function __construct(EntityManagerInterface $em, Mapping\ClassMetadata $metaData = null)
    {
        parent::__construct($em, $metaData == null ?
            new Mapping\ClassMetadata(Message::class) :
            $metaData);
    }

    /**
     * @param Message $message
     * @return bool
     */
    public function insert(Message $message)
    {
        try {
            $this->_em->persist($message);
            $this->_em->flush();
            return true;
        } catch (OptimisticLockException $e) {
            return false;
        }
    }
}
