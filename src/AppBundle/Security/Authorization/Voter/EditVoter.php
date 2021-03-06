<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Security\Authorization\Voter;

use AppBundle\Entity\Event;
use AppBundle\Entity\Place;
use AppBundle\Entity\User;
use Gedmo\Blameable\Blameable;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class EditVoter extends Voter
{
    const UPDATE = 'update';
    const REMOVE = 'remove';

    private $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute
     *                          An attribute
     * @param mixed  $subject
     *                          The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        // If the attribute isn't one we support, return false.
        if (!in_array($attribute, [self::UPDATE, self::REMOVE], true)) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     *
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            // The user must be logged in; if not, deny access.
            return false;
        }

        if (!$subject instanceof Blameable) {
            return false;
        }

        switch ($attribute) {
            case self::UPDATE:
                return $this->canUpdate($subject, $user);
            case self::REMOVE:
                return $this->canRemove($subject, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * Check if a user can edit a Blameable entity.
     */
    private function canEdit(Blameable $entity, User $user)
    {
        // Hack!
        if ($entity instanceof Event) {
            if ($entity->getFeed()) {
                // Events from feed can only be edited by owner.
                return $entity->getCreatedBy()->getId() === $user->getId();
            }
            if ($this->hasRole($user, 'ROLE_EVENT_ADMIN')) {
                // ROLE_EVENT_ADMIN can edit all events.
                return true;
            }
        }
        // Hack!
        if ($entity instanceof Place) {
            if ($this->hasRole($user, 'ROLE_PLACE_ADMIN')) {
                // ROLE_PLACE_ADMIN can edit all places.
                return true;
            }
        }

        $createdByUser = $entity->getCreatedBy();
        if (!$createdByUser) {
            return false;
        }

        if ($user->getId() === $createdByUser->getId()) {
            return true;
        }

        // Check user's groups.
        $groups = $user->getGroups();
        $createdByGroups = $createdByUser->getGroups();
        if (!$groups || !$createdByGroups) {
            return false;
        }

        foreach ($groups as $group) {
            if ($createdByGroups->contains($group)) {
                return true;
            }
        }

        return false;
    }

    private function canUpdate(Blameable $entity, User $user)
    {
        return $this->canEdit($entity, $user);
    }

    private function canRemove(Blameable $entity, User $user)
    {
        if ($entity instanceof Event) {
            if ($entity->getFeed()) {
                // Events from feed can only be deleted by owner or event administrator.
                return $this->hasRole($user, 'ROLE_EVENT_ADMIN') || $entity->getCreatedBy()->getId() === $user->getId();
            }
        }

        return $this->canEdit($entity, $user);
    }

    private function hasRole(User $user, $roleName)
    {
        $roles = $this->getUserRoles($user);

        return array_filter($roles, function (Role $role) use ($roleName) {
            return $role->getRole() === $roleName;
        });
    }

    private function getTokenRoles(TokenInterface $token)
    {
        return $this->roleHierarchy->getReachableRoles($token->getRoles());
    }

    private function getUserRoles(User $user)
    {
        $roles = array_map(function ($name) {
            return new Role($name);
        }, $user->getRoles());

        return $this->roleHierarchy->getReachableRoles($roles);
    }
}
