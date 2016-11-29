<?php

namespace AppBundle\Security\Authorization\Voter;

use Gedmo\Blameable\Blameable;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
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

    if (!$subject instanceof Blameable) {
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
    if ($this->hasRole($token, 'ROLE_ADMIN')) {
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
  private function hasRole(TokenInterface $token, $roleName) {
    if (NULL === $this->roleHierarchy) {
      return in_array($roleName, $token->getRoles(), TRUE);
    }

    foreach ($this->roleHierarchy->getReachableRoles($token->getRoles()) as $role) {
      if ($roleName === $role->getRole()) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
