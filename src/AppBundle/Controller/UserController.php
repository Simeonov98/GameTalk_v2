<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Service\Message\MessageServiceInterface;
use AppBundle\Service\User\UserServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    /**
     * @var UserServiceInterface
     */
    private $userService;

    /**
     * @var MessageServiceInterface
     */
    private $messageService;

    public function __construct(UserServiceInterface $userService,MessageServiceInterface $messageService)
    {
        $this->userService = $userService;
        $this->messageService = $messageService;
    }
    /**
     * @Route("register", name="user_register",methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function register(Request $request)
    {

        return $this->render('users/register.html.twig',
            ['form' => $this->createForm(UserType::class)->createView()]);
    }

    /**
     * @Route("register", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function registerProcess(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);


        if (null !== $this->userService->findOneByEmail($form['email']->getData())) {
            $email= $this->userService->findOneByEmail($form['email']->getData())->getEmail();
            $this->addFlash("errors", "Email $email already taken!");
            return $this->returnRegisterView($user);
        }

        if ($form['password']['first']->getData() !== $form['password']['second']->getData()){
            $this->addFlash("errors","Password mismatch!");
            return $this->returnRegisterView($user);
        }

        $this->userService->save($user);
        return $this->redirectToRoute("security_login");
    }

    /**
     *
     * @Route("/profile", name="user_profile")
     */
    public function profile()
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $currentUser = $userRepository->find($this->getUser());

        return $this->render("users/profile.html.twig",
            ['user' => $currentUser]);
    }
    /**
     * @Route("/profile/edit",name="user_edit_profile", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @return Response
     */
    public function edit()
    {
        $currentUser = $this->userService->currentUser();
        return $this->render("users/edit.html.twig",
            [
                'user' => $currentUser,
                'form' => $this->createForm(UserType::class)
                    ->createView()
            ]
        );

    }

    /**
     * @Route("/profile/{id}/edit", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return Response
     */
    public function editProcess(Request $request)
    {
        $currentUser = $this->userService->currentUser();
        $form = $this->createForm(UserType::class, $currentUser);
        $form->handleRequest($request);
        if ($currentUser->getEmail() === $request->request->get('email')) {
            $form->remove('email');
        }
        $form->remove('password');

        $this->uploadFile($form, $currentUser);

        $this->userService->save($currentUser);

        return $this->redirect("user_profile");

    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {
        throw new Exception("Logout failed!");
    }
    /**
     * @param FormInterface $form
     * @param User $user
     */
    private function uploadFile(FormInterface $form, User $user)
    {
        /**
         * @var UploadedFile $file
         */
        $file = $form['image']->getData();
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();

        if ($file) {
            $file->move(
                $this->getParameter('users_directory'),
                $fileName
            );

            $user->setProfilePic($fileName);
        }
    }

    /**
     * @param User $user
     * @return Response
     */
    private function returnRegisterView(User $user): Response
    {
        return $this->render("users/register.html.twig",
            [
                'user' => $user,
                'form' => $this->createForm(UserType::class)->createView()
            ]);
    }

}
