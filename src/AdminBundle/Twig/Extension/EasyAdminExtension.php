<?php

namespace AdminBundle\Twig\Extension;

use AppBundle\Security\Authorization\Voter\EditVoter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Translation\TranslatorInterface;

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

  /**
   * @var \Symfony\Component\CssSelector\XPath\TranslatorInterface
   */
  private $translator;

  public function __construct(TokenStorageInterface $tokenStorage, EditVoter $voter, TranslatorInterface $translator) {
    $this->tokenStorage = $tokenStorage;
    $this->voter = $voter;
    $this->translator = $translator;
  }

  /**
   *
   */
  public function getFunctions() {
    return [
      new \Twig_Function('can_perform_action', [$this, 'canPerformAction'], ['is_safe' => ['all']]),
      new \Twig_Function('get_field_help', [$this, 'getFieldHelp'], ['is_safe' => ['all']]),
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

  public function getFieldHelp(array $context) {
    if (isset($context['full_name'])) {
      // Remove numeric indexing (…[87]… -> ……);
      $key = preg_replace('/\[[0-9]+\]/', '', $context['full_name']);
      // Replace [] with .
      $key = str_replace(['[', ']'], ['.', ''], $key);
      $key = preg_replace('/^[a-z0-9]+\./', 'app.\0help.', $key);
      $translated = $this->translator->trans($key);

      if ($translated !== $key) {
        return $translated;
      }
    }
    return NULL;
  }
}
