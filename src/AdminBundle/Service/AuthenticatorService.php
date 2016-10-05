<?php

namespace AdminBundle\Service;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Service to set a user as authenticated.
 */
class AuthenticatorService {
  /**
   * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
   */
  private $tokenStorage;

  /**
   * @var array
   */
  private $configuration;

  public function __construct(TokenStorageInterface $tokenStorage, array $configuration) {
    $this->tokenStorage = $tokenStorage;
    $this->configuration = $configuration;
  }

  public function authenticate(User $user) {
    $firewall = isset($this->configuration['firewall']) ? $this->configuration['firewall'] : 'main';
    $token = new UsernamePasswordToken($user, NULL, $firewall);
    $this->tokenStorage->setToken($token);
  }
}
