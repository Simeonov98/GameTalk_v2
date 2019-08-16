<?php

namespace AppBundle\Service\Role;
interface RoleServiceInterface
{
    public function findOneBy(string $criteria);
}