<?php
namespace AppBundle\Service\Article;


use AppBundle\Entity\Article;
use Doctrine\Common\Collections\ArrayCollection;

interface ArticleServiceInterface
{

    /**
     * @return ArrayCollection|Article[]
     */
    public function getAll();
    public function create(Article $article): bool;
    public function edit(Article $article): bool;
    public function delete(Article $article): bool;
    public function getOne(int $id) : ?Article;
    public function getAllArticlesByAuthor();
}