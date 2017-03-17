<?php

namespace AdminBundle\Twig\Extension;

use AppBundle\Security\Authorization\Voter\EditVoter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class TwigExtension.
 *
 * @package AdminBundle\Twig\Extension
 */
class EasyAdminExtension extends \Twig_Extension {
  /**
   * @var \AdminBundle\Twig\Extension\TokenStorageInterface
   */
  private $tokenStorage;

  /**
   * @var \AppBundle\Security\Authorization\Voter\EditVoter
   */
  private $voter;

  public function __construct(TokenStorageInterface $tokenStorage, EditVoter $voter) {
    $this->tokenStorage = $tokenStorage;
    $this->voter = $voter;
  }

  /**
   *
   */
  public function getFunctions() {
    return [
      new \Twig_Function('can_perform_action', [$this, 'canPerformAction'], ['is_safe' => ['all']]),
    ];
  }

  public function getTests() {
    return [
      new \Twig_Test('instanceof', function ($var, $class) {
        return $var instanceof $class;
      }),
    ];
  }

  public function canPerformAction($action, $subject) {
    $token = $this->tokenStorage->getToken();
    if (!$token) {
      return false;
    }
    switch ($action) {
      case 'clone':
      case 'edit':
        $action = EditVoter::UPDATE;
        break;
      case 'delete':
        $action = EditVoter::REMOVE;
        break;
      case 'show':
        return true;
    }

    return $this->voter->vote($token, $subject, [$action]) == VoterInterface::ACCESS_GRANTED;
  }
}
