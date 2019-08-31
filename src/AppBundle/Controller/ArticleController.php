<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\User;
use AppBundle\Form\ArticleType;
use AppBundle\Service\Article\ArticleServiceInterface;
use AppBundle\Service\Comment\CommentServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Article controller.
 *
 * @Route("article")
 */
class ArticleController extends Controller
{
    /**
     * @var ArticleServiceInterface
     */
    private $articleService;

    private $commentService;

    /**
     * ArticleController constructor.
     * @param ArticleServiceInterface $articleService
     * @param CommentServiceInterface $commentService
     */
    public function __construct(ArticleServiceInterface $articleService,
                                CommentServiceInterface $commentService)
    {
        $this->articleService = $articleService;
        $this->commentService = $commentService;
    }


    /**
     * @Route("/create", name="article_create", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @return Response
     */
    public function create()
    {
        return $this->render('article/create.html.twig',
            [
                'form' => $this
                    ->createForm(ArticleType::class)
                    ->createView()
            ]);
    }

    /**
     * @Route("/create", methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function createProcess(Request $request)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if (strlen($form['title']->getData()) < 4) {
            $this->addFlash("errors", "Article title must be at least 4 characters long");
            return $this->returnArticleView($article);
        }
        if (strlen($form['title']->getData()) > 50) {
            $this->addFlash("errors", "Article title cannot be longer than 50 characters");
            return $this->returnArticleView($article);
        }
        if (strlen($form['content']->getData()) < 50) {
            $this->addFlash("errors", "Article content must be at least 50 characters long");
            return $this->returnArticleView($article);
        }
        if ($form['image'] == null) {
            $this->addFlash("errors", "Article must have an image");
            $this->returnArticleView($article);
        }
        if ($form['genre']->getData() != "Action" && $form['genre']->getData() != "Adventure" &&
            $form['genre']->getData() != "Simulation" && $form['genre']->getData() != "Strategy" &&
            $form['genre']->getData() != "Sports" && $form['genre']->getData() != "MMO") {
            $this->addFlash("errors", "Article genre should be one of the following: Action, Adventure, Simulation, Strategy, Sports or MMO");
            return $this->returnArticleView($article);
        }

        $this->uploadFile($form, $article);

        $this->articleService->create($article);

        $this->addFlash("info", "Article successfully created!");
        return $this->redirectToRoute("blog_index");

    }

    /**
     * @Route("/edit/{id}", name="article_edit", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param int $id
     * @return Response
     */
    public function edit(int $id)
    {
        /** @var Article $article */
        $article = $this->articleService->getOne($id);

        if (null === $article) {
            return $this->redirectToRoute("blog_index");
        }
        if (!$this->isAuthorOrAdmin($article)) {
            return $this->redirectToRoute("blog_index");
        }


        return $this->render('article/edit.html.twig',
            [
                'form' => $this
                    ->createForm(ArticleType::class)
                    ->createView(),
                'article' => $article
            ]);
    }

    /**
     * @Route("/edit/{id}", methods={"POST"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function editProcess(Request $request, int $id)
    {
        /** @var Article $article */
        $article = $this->articleService->getOne($id);

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        $this->uploadFile($form, $article);
        $this->articleService->edit($article);

        return $this->redirectToRoute("blog_index");
    }

    /**
     * @Route("/delete/{id}", name="article_delete", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param int $id
     * @return Response
     */
    public function delete(int $id)
    {
        /** @var Article $article */
        $article = $this->articleService->getOne($id);

        if (null == $article) {
            return $this->redirectToRoute("blog_index");
        }

        if (!$this->isAuthorOrAdmin($article)) {
            return $this->redirectToRoute("blog_index");
        }

        return $this->render('article/delete.html.twig',
            [
                'form' => $this->createForm(ArticleType::class)->createView(),
                'article' => $article
            ]);
    }

    /**
     * @Route("/delete/{id}", methods={"POST"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function deleteProcess(Request $request, int $id)
    {
        /** @var Article $article */
        $article = $this->articleService->getOne($id);

        $form = $this->createForm(ArticleType::class, $article);
        $form->remove('image');
        $form->remove('genre');
        $form->handleRequest($request);

        $this->articleService->delete($article);

        return $this->redirectToRoute("blog_index");

    }


    /**
     * @param FormInterface $form
     * @param Article $article
     * @return Response
     */
    private function uploadFile(FormInterface $form, Article $article)
    {
        /**
         * @var UploadedFile $file
         */
        $file = $form['image']->getData();

        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
        if ($file ===null){
            $this->addFlash("errors","Article must have an image");
            return $this->returnArticleView($article);
        }
        if ($file) {
            $file->move(
                $this->getParameter('article_directory'),
                $fileName
            );

            $article->setImage($fileName);
        }

    }

    /**
     * @Route("/article/{id}", name="article_view")
     *
     * @param $id
     * @return Response
     */
    public function view($id)
    {
        $article = $this->articleService->getOne($id);


        if (null === $article) {
            return $this->redirectToRoute("blog_index");
        }

        $article->setViewCount($article->getViewCount() + 1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        $comments = $this->commentService->getAllByArticleId($id);


        return $this->render("article/view.html.twig",
            [
                'article' => $article,
                'comments' => $comments
            ]);
    }

    /**
     * @param Article $article
     * @return bool
     */
    private function isAuthorOrAdmin(Article $article)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser->isAuthor($article) && !$currentUser->isAdmin()) {
            return false;
        }
        return true;
    }


    /**
     * @Route("/articles/my_articles", name="my_articles")
     * @return Response
     */
    public function getAllArticleByUsers()
    {
        $articles = $this->articleService->getAllArticlesByAuthor();

        return $this->render(
            "article/myArticles.html.twig",
            [
                'articles' => $articles
            ]
        );
    }

    /**
     * @param Article $article
     * @return Response
     */
    private function returnArticleView(Article $article): Response
    {
        return $this->render("article/create.html.twig",
            [
                'article' => $article,
                'form' => $this->createForm(ArticleType::class)->createView()
            ]);
    }
}
