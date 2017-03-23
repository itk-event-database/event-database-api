<?php

namespace AppBundle\Security\Authorization\Voter;

use AppBundle\Entity\Event;
use Gedmo\Blameable\Blameable;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use AppBundle\Entity\User;

/**
 *
 */
class EditVoter extends Voter {
  const UPDATE = 'update';
  const REMOVE = 'remove';

  private $roleHierarchy;

  /**
   *
   */
  public function __construct(RoleHierarchyInterface $roleHierarchy) {
    $this->roleHierarchy = $roleHierarchy;
  }

  /**
   * Determines if the attribute and subject are supported by this voter.
   *
   * @param string $attribute
   *   An attribute
   * @param mixed $subject
   *   The subject to secure, e.g. an object the user wants to access or any other PHP type
   *
   * @return bool True if the attribute and subject are supported, false otherwise
   */
  protected function supports($attribute, $subject) {
    // If the attribute isn't one we support, return false.
    if (!in_array($attribute, [self::UPDATE, self::REMOVE])) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Perform a single access check operation on a given attribute, subject and token.
   *
   * @param string $attribute
   * @param mixed $subject
   * @param TokenInterface $token
   *
   * @return bool
   */
  protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
    if ($this->hasRole($this->getTokenRoles($token), 'ROLE_ADMIN')) {
      return TRUE;
    }

    $user = $token->getUser();
    if (!$user instanceof User) {
      // The user must be logged in; if not, deny access.
      return FALSE;
    }

    if (!$subject instanceof Blameable) {
      return FALSE;
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
  private function canEdit(Blameable $entity, User $user) {
    // Hack!
    if ($entity instanceof Event) {
      $userRoles = $this->getUserRoles($user);
      if ($this->hasRole($userRoles, 'ROLE_EVENT_ADMIN')) {
        // ROLE_EVENT_ADMIN can edit all events.
        return TRUE;
      }
    }

    $createdByUser = $entity->getCreatedBy();
    if (!$createdByUser) {
      return FALSE;
    }

    if ($user->getId() === $createdByUser->getId()) {
      return TRUE;
    }

    // Check user's groups.
    $groups = $user->getGroups();
    $createdByGroups = $createdByUser->getGroups();
    if (!$groups || !$createdByGroups) {
      return FALSE;
    }

    foreach ($groups as $group) {
      if ($createdByGroups->contains($group)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   *
   */
  private function canUpdate(Blameable $entity, User $user) {
    return $this->canEdit($entity, $user);
  }

  /**
   *
   */
  private function canRemove(Blameable $entity, User $user) {
    return $this->canEdit($entity, $user);
  }

  /**
   *
   */
  private function hasRole(array $roles, $roleName) {
    return array_filter($roles, function (Role $role) use ($roleName) {
      return $role->getRole() === $roleName;
    });
  }

  private function getTokenRoles(TokenInterface $token) {
    return $this->roleHierarchy->getReachableRoles($token->getRoles());
  }

  private function getUserRoles(User $user) {
    $roles = array_map(function ($name) {
      return new Role($name);
    }, $user->getRoles());

    return $this->roleHierarchy->getReachableRoles($roles);
  }
}
