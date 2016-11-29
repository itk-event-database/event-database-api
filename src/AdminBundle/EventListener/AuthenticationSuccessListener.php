<?php

namespace AdminBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessListener {
  /**
   * @param AuthenticationSuccessEvent $event
   */
  public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event) {
    $user = $event->getUser();

    if (!$user instanceof UserInterface) {
      return;
    }

    $data = $event->getData();
    $data['user'] = [
      'username' => $user->getUsername(),
    ];
    $event->setData($data);
  }
}
