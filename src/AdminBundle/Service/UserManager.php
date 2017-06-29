<?php

namespace AdminBundle\Service;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class UserManager
{
    private $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    public function canEdit(User $user, TokenInterface $token)
    {
        if (!$this->hasRole($token, 'ROLE_USER_EDITOR')) {
            return false;
        }

        $userRoles = array_map(function ($name) {
            return new Role($name);
        }, $user->getRoles());
        $roles = $this->roleHierarchy->getReachableRoles($userRoles);
        foreach ($roles as $role) {
            if (in_array($role->getRole(), ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])) {
                return false;
            }
        }

        return true;
    }

    private function hasRole(TokenInterface $token, $roleName)
    {
        if (null === $this->roleHierarchy) {
            return in_array($roleName, $token->getRoles(), true);
        }

        foreach ($this->roleHierarchy->getReachableRoles($token->getRoles()) as $role) {
            if ($roleName === $role->getRole()) {
                return true;
            }
        }

        return false;
    }
}
