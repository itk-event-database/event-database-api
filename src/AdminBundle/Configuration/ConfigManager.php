<?php

namespace AdminBundle\Configuration;

use JavierEguiluz\Bundle\EasyAdminBundle\Configuration\ConfigManager as BaseConfigManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

class ConfigManager extends BaseConfigManager {
  /**
   * @var ContainerInterface
   */
  protected $container;

  /**
   * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
   */
  protected $tokenStorage;

  /**
   * @var \Symfony\Component\Security\Core\Role\RoleHierarchyInterface
   */
  protected $roleHierarchy;

  public function __construct(ContainerInterface $container, TokenStorageInterface $tokenStorage, RoleHierarchyInterface $roleHierarchy) {
    parent::__construct($container);
    $this->container = $container;
    $this->tokenStorage = $tokenStorage;
    $this->roleHierarchy = $roleHierarchy;
  }

  public function getBackendConfig($propertyPath = NULL) {
    $config = parent::getBackendConfig($propertyPath);

    if ($propertyPath == 'design.menu') {
      $config = self::array_filter_recursive($config, function ($item) {
        $roles = isset($item['roles']) ? $item['roles'] : (isset($item['role']) ? $item['role'] : NULL);
        if (!$roles) {
          return TRUE;
        }
        if (!is_array($roles)) {
          $roles = [$roles];
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
          return FALSE;
        }

        $userRoles = $this->roleHierarchy->getReachableRoles($token->getRoles());

        return array_intersect($roles, array_map(function (RoleInterface $role) {
          return $role->getRole();
        }, $userRoles));
      });
    }

    return $config;
  }

  private function hasRole(TokenInterface $token, array $roleNames) {
    $roles = $token->getRoles();

    //    foreach ($this->roleHierarchy->getReachableRoles($token->getRoles()) as $role) {
    //      if ($roleName === $role->getRole()) {
    //        return TRUE;
    //      }
    //    }

    return FALSE;
  }

  // @see http://php.net/manual/en/function.array-filter.php#87581

  private static function array_filter_recursive(array $input, callable $callback) {
    foreach ($input as &$value) {
      if (is_array($value)) {
        $value = self::array_filter_recursive($value, $callback);
      }
    }

    return array_filter($input, $callback);
  }

}
