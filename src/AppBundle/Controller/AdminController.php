<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Service\Role\RoleServiceInterface;
use AppBundle\Service\User\UserServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    /**
     * @var UserServiceInterface
     */
    private $userService;
    /**
     * @var RoleServiceInterface
     */
    private $roleService;

    /**
     * AdminController constructor.
     * @param UserServiceInterface $userService
     * @param RoleServiceInterface $roleService
     */
    public function __construct(UserServiceInterface $userService, RoleServiceInterface $roleService)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    /**
     * @Route("/admin/users", name="admin_users", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('admin/users.html.twig',
            [
                'users' => $this->userService->getAll()
            ]
        );
    }



}
