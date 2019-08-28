<?php

namespace AppBundle\Service\User;
use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use AppBundle\Service\Encryption\ArgonEncryption;
use AppBundle\Service\Role\RoleServiceInterface;
use Symfony\Component\Security\Core\Security;

class UserService implements UserServiceInterface
{

    private $security;

    private $userRepository;

    private $roleService;

    private $encryptionService;

    public function __construct(Security $security,
                                UserRepository $userRepository,
                                ArgonEncryption $encryptionService,
                                RoleServiceInterface $roleService)
    {
        $this->encryptionService = $encryptionService;
        $this->security = $security;
        $this->userRepository = $userRepository;
        $this->roleService = $roleService;
    }

    /**
     * @param string $email
     * @return User|null|object
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email'=>$email]);
    }

    public function save(User $user): bool
    {

        $passwordHash = $this->encryptionService->hash($user->getPassword());
        $user->setPassword($passwordHash);
        $userRole=$this->roleService->findOneBy("ROLE_USER");
        $user->addRole($userRole);
        $user->setProfilePic("");

        return $this->userRepository->insert($user);
    }

    /**
     * @param int $id
     * @return User|null|object
     */
    public function findOneById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     * @param User $user
     * @return User|null|object
     */
    public function findOne(User $user): ?User
    {
        return $this->userRepository->find($user);
    }

    /**
     * @return null|User|object
     */
    public function currentUser(): ?User
    {
        return $this->security->getUser();
    }

    public function update(User $user): bool
    {
        return $this->userRepository->update($user);
    }
    public function getAll()
    {
        return $this->userRepository->findAll();
    }
}