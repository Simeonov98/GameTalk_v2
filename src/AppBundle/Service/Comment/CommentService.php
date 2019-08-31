<?php


namespace AppBundle\Service\Comment;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\ORMException;
use AppBundle\Entity\Comment;
use AppBundle\Repository\CommentRepository;
use AppBundle\Service\Article\ArticleServiceInterface;
use AppBundle\Service\User\UserServiceInterface;

class CommentService implements CommentServiceInterface
{
    /**
     * @var UserServiceInterface
     */
    private $userService;
    private $commentRepository;
    private $articleService;

    /**
     * CommentService constructor.
     * @param CommentRepository $commentRepository
     * @param UserServiceInterface $userService
     * @param ArticleServiceInterface $articleService
     */
    public function __construct(CommentRepository $commentRepository,
                                UserServiceInterface $userService,
                                ArticleServiceInterface $articleService)
    {
        $this->commentRepository = $commentRepository;
        $this->userService = $userService;
        $this->articleService = $articleService;
    }

    /**
     * @param int $articleId
     * @return Comment[]
     */
    public function getAllByArticleId(int $articleId)
    {
        $article = $this->articleService->getOne($articleId);
        return $this
            ->commentRepository
            ->findBy(['article' => $article], ['dateAdded' => 'DESC']);
    }

    public function getOne(): ?Comment
    {
        // TODO: Implement getOne() method.
    }

    /**
     * @param Comment $comment
     * @param int $articleId
     * @return bool
     * @throws ORMException
     */
    public function create(Comment $comment, int $articleId): bool
    {
        $comment
            ->setAuthor($this->userService->currentUser())
            ->setArticle($this->articleService->getOne($articleId));

        return $this->commentRepository->insert($comment);
    }

    /**
     * @param ArrayCollection|Comment[]
     * @return bool
     */
    public function delete($comment): bool
    {
        return $this->commentRepository->remove($comment);
    }


}