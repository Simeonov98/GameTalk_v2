<?php

namespace AppBundle\Controller;

use AppBundle\Service\User\UserServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    /**
     * @var UserServiceInterface
     */
    private $userService;

    /**
     * AdminController constructor.
     * @param UserServiceInterface $userService
     */
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Route("/admin/users",name="admin_users",methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @return Response
     */
    public function indexAction()
    {
        $this->userService->getAll();
        return $this->render('admin/users.html.twig', array('name' => $name));
    }
}
