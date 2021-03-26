<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Cache\CacheManager;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EasyAdminConfigManager extends ConfigManager
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    private $cache = [];

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        CacheManager $cacheManager,
        PropertyAccessorInterface $propertyAccessor,
        array $originalBackendConfig = [],
        $debug = false
    ) {
        parent::__construct($cacheManager, $propertyAccessor, $originalBackendConfig, $debug);
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    public function getBackendConfig($propertyPath = null)
    {
        $cacheKey = $propertyPath ?: '';
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $config = parent::getBackendConfig($propertyPath);

        $token = $this->tokenStorage->getToken();
        if ($token) {
            if (is_array($config)) {
                // Filter config by roles.
                $config = self::arrayFilterRecursive($config, function ($item) {
                    // If key "roles" is not set or the value is an associative array, we want to keep the value.
                    if (!isset($item['roles']) || self::isAssoc($item['roles'])) {
                        return true;
                    }

                    $roles = $item['roles'];
                    if (!is_array($roles)) {
                        $roles = [$roles];
                    }

                    return $this->hasRole($roles);
                });

                if ('design.menu' === $propertyPath) {
                    $this->reindexMenu($config);
                }

                $this->reConfigEventAccessField($config);
            }
        }

        $this->cache[$cacheKey] = $config;

        return $config;
    }

    /**
     * Reconfigure the Event forms depending on logged in users roles.
     *
     * @param array $config
     */
    private function reConfigEventAccessField(array &$config): void
    {
        // If the editor doesn't have the rights to create both "full access" and
        // "limited access" events we remove field from the "new" and "edit" pages.
        if (!$this->hasRole(['ROLE_FULL_ACCESS_EVENT_EDITOR']) || !$this->hasRole(['ROLE_LIMITED_ACCESS_EVENT_EDITOR'])) {
            if (isset($config['entities']['Event']['edit']['fields']['hasFullAccess'])) {
                unset($config['entities']['Event']['edit']['fields']['hasFullAccess']);
            }

            if (isset($config['entities']['Event']['new']['fields']['hasFullAccess'])) {
                unset($config['entities']['Event']['new']['fields']['hasFullAccess']);
            }
        }
    }

    private function reindexMenu(array &$config, $menuIndex = null)
    {
        $config = array_values($config);
        foreach ($config as $index => &$item) {
            if (null === $menuIndex) {
                $item['menu_index'] = $index;
                if (isset($item['children'])) {
                    $this->reindexMenu($item['children'], $index);
                }
            } else {
                $item['menu_index'] = $menuIndex;
                $item['submenu_index'] = $index;
            }
        }
    }

    private function hasRole(array $roleNames)
    {
        foreach ($roleNames as $roleName) {
            if ($this->authorizationChecker->isGranted($roleName)) {
                return true;
            }
        }

        return false;
    }

    // @see http://php.net/manual/en/function.array-filter.php#87581
    private static function arrayFilterRecursive(array $input, callable $callback)
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = self::arrayFilterRecursive($value, $callback);
            }
        }

        return array_filter($input, $callback);
    }

    // @see http://stackoverflow.com/a/173479
    private static function isAssoc(array $arr)
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
