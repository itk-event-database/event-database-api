<?php

namespace AdminBundle\Service;

/**
 *
 */
class RolesHelper
{
  /**
   * @var array
   */
    private $roleHierarchy;

  /**
   * @param array $roleHierarchy
   */
    public function __construct(array $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

  /**
   *
   */
    public function getRoles()
    {
        $allRoles = [];

        foreach ($this->roleHierarchy as $role => $roles) {
            $allRoles[$role] = $role;
            array_walk_recursive($roles, function ($role) use (&$allRoles) {
                $allRoles[$role] = $role;
            });
        }

        return $allRoles;
    }
}
