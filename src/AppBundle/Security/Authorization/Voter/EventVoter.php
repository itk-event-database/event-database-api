<?php

namespace AppBundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\User;

class EventVoter extends Voter {
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
        if ($event->getCreatedBy() && $user->getId() === $event->getCreatedBy()->getId()) {
          return true;
        }

        break;
    }

    return false;
  }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        // TODO: Implement supports() method.
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
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        // TODO: Implement voteOnAttribute() method.
    }
}
