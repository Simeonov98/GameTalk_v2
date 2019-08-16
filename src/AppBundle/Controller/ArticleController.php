<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Form\ArticleType;
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
     * Lists all article entities.
     *
     * @Route("/", name="article_index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $articles = $em->getRepository('AppBundle:Article')->findAll();

        return $this->render('article/index.html.twig', array(
            'articles' => $articles,
        ));
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
     * @return RedirectResponse
     * @throws \Exception
     */
    public function createProcess(Request $request)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        $this->uploadFile($form, $article);


        $this->articleService->create($article);

        $this->addFlash("info", "Article successfully created!");
        return $this->redirectToRoute("blog_index");

    }

    /**
     * Finds and displays a article entity.
     *
     * @Route("/article/{id}", name="article_view",methods={"GET"})
     * @param Article $article
     * @return Response
     */
    public function showAction(Article $article)
    {
        $deleteForm = $this->createDeleteForm($article);

        return $this->render('article/show.html.twig', array(
            'article' => $article,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing article entity.
     *
     * @Route("/edit/{id}", name="article_edit", methods={"GET", "POST"})
     * @param Request $request
     * @param Article $article
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, Article $article)
    {
        $deleteForm = $this->createDeleteForm($article);
        $editForm = $this->createForm('AppBundle\Form\ArticleType', $article);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('article_edit', array('id' => $article->getId()));
        }

        return $this->render('article/edit.html.twig', array(
            'article' => $article,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a article entity.
     *
     * @Route("/{id}", name="article_delete",methods={"DELETE"})
     * @param Request $request
     * @param Article $article
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Article $article)
    {
        $form = $this->createDeleteForm($article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($article);
            $em->flush();
        }

        return $this->redirectToRoute('article_index');
    }

    /**
     * Creates a form to delete a article entity.
     *
     * @param Article $article The article entity
     *
     * @return FormInterface
     */
    private function createDeleteForm(Article $article)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('article_delete', array('id' => $article->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    /**
     * @param FormInterface $form
     * @param Article $article
     */
    private function uploadFile(FormInterface $form, Article $article)
    {
        /**
         * @var UploadedFile $file
         */
        $file = $form['image']->getData();
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();

        if ($file) {
            $file->move(
                $this->getParameter('article_directory'),
                $fileName
            );

            $article->setImage($fileName);
        }
    }
}
