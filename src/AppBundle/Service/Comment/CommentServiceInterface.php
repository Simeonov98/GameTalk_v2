<?php


namespace AppBundle\Service\Comment;


use AppBundle\Entity\Comment;
use Symfony\Component\HttpFoundation\Request;

interface CommentServiceInterface
{

    public function create(Comment $comment,int $articleId):bool;

    /**
     * @param int $articleId
     * @return Comment[]
     */
    public function getAllByArticleId(int $articleId);
    public function getOne(): ?Comment;
}