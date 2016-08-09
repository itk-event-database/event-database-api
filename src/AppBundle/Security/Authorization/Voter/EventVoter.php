<?php

namespace AppBundle\Security\Authorization\Voter;

use AppBundle\Entity\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\User;

class EventVoter extends Voter {
  const UPDATE = 'update';
  const REMOVE = 'remove';

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
    // if the attribute isn't one we support, return false
    if (!in_array($attribute, array(self::UPDATE, self::REMOVE))) {
      return false;
    }

    if (!$subject instanceof Event) {
      return false;
    }

    return true;
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
    $user = $token->getUser();

    if (!$user instanceof User) {
      // the user must be logged in; if not, deny access
      return false;
    }

    $event = $subject;

    switch ($attribute) {
      case self::UPDATE:
        return $this->canUpdate($event, $user);
      case self::REMOVE:
        return $this->canRemove($event, $user);
    }

    throw new \LogicException('This code should not be reached!');
  }

  private function isOwner(Event $event, User $user) {
    // @TODO: Check user's groups as well.
    return $event->getCreatedBy() && $user->getId() === $event->getCreatedBy()->getId();
  }

  private function canUpdate(Event $event, User $user) {
    return $this->isOwner($event, $user);
  }

  private function canRemove(Event $event, User $user) {
    return $this->isOwner($event, $user);
  }
}
