<?php

namespace AppBundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\User;

class EventVoter extends AbstractVoter {
  const EDIT = 'edit';
  const DELETE = 'delete';

  protected function getSupportedAttributes() {
    return [ self::EDIT, self::DELETE ];
  }

  protected function getSupportedClasses() {
    return [ 'AppBundle\Entity\Event' ];
  }

  protected function isGranted($attribute, $event, $user = null) {
    if (!$user instanceof UserInterface) {
      return false;
    }

    if (!$user instanceof User) {
      throw new \LogicException('The user is somehow not our User class!');
    }

    switch($attribute) {
      case self::EDIT:
      case self::DELETE:
        // this assumes that the data object has a getOwner() method
        // to get the entity of the user who owns this data object
        if ($user->getId() === $event->getCreatedBy()->getId()) {
          return true;
        }

        break;
    }

    return false;
  }
}
