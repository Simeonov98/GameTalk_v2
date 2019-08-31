<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Form\CommentType;
use AppBundle\Service\Article\ArticleServiceInterface;
use AppBundle\Service\Comment\CommentServiceInterface;
use AppBundle\Service\User\UserServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends Controller
{
    /**
     * @var ArticleServiceInterface
     */
    private $articleService;

    /** @var CommentServiceInterface */
    private $commentService;

    /**
     * @var UserServiceInterface
     */
    private $userService;

    public function __construct(ArticleServiceInterface $articleService,
                                CommentServiceInterface $commentService,UserServiceInterface $userService)
    {
        $this->articleService = $articleService;
        $this->commentService = $commentService;
        $this->userService = $userService;
    }

    /**
     *
     * @Route("/comment/create/{id}", name="comment_create", methods={"POST"})
     * @param Request $request
     * @param $id
     * @return Response
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function create(Request $request, $id)
    {

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if (strlen($form['content']->getData())<=5){
            $this->addFlash("errors","Your comment must contain at least 5 symbols");
            return $this->redirectToRoute('article_view',['id' => $id]);
        }

        $this->addFlash("message", "Comment created successfully");
        $this->commentService->create($comment,$id);
        return $this->redirectToRoute('article_view',['id' => $id]);
    }


    /**
     *
     * @Route("/user/{id}/messsage", name="user_message", methods={"GET"})
     * @param $id
     * @return Response
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function addUserMessage($id)
    {
        return $this->render('users/message.html.twig',
            [
                'user' => $this->userService->findOneById($id)
            ]);
    }


    public function getAllCommentsByArticleId()
    {

    }
}
