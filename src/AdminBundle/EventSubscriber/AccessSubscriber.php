<?php

namespace AdminBundle\EventSubscriber;

use AdminBundle\Entity\Feed;
use AdminBundle\Service\UserManager;
use AppBundle\Entity\Event;
use AppBundle\Entity\Group;
use AppBundle\Entity\Tag;
use AppBundle\Entity\UnknownTag;
use AppBundle\Entity\User;
use JavierEguiluz\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Proxies\__CG__\AppBundle\Entity\Place;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class AccessSubscriber implements EventSubscriberInterface {
  private $tokenStorage;
  private $roleHierarchy;
  private $userManager;

  public function __construct(TokenStorageInterface $tokenStorage, RoleHierarchyInterface $roleHierarchy, UserManager $userManager) {
    $this->tokenStorage = $tokenStorage;
    $this->roleHierarchy = $roleHierarchy;
    $this->userManager = $userManager;
  }

  public static function getSubscribedEvents() {
    return [
      EasyAdminEvents::PRE_LIST => ['preList'],
      EasyAdminEvents::PRE_EDIT => ['preEdit'],
    ];
  }

  public function preList(GenericEvent $event) {
    $entity = $event->getArgument('entity');
    $class = $entity['class'];
    if (isset(self::$requiredRoles[$class])) {
        $this->requireRole(self::$requiredRoles[$class]);
    }
  }

  public function preEdit(GenericEvent $event) {
    $entity = $event->getArgument('entity');
    $class = $entity['class'];
    if (isset(self::$requiredRoles[$class])) {
      $this->requireRole(self::$requiredRoles[$class]);
    }

    $request = $event->getArgument('request');
    $easyadmin = $request->attributes->get('easyadmin');
    $entity = $easyadmin['item'];

    if ($entity instanceof User) {
      if (!$this->userManager->canEdit($entity, $this->tokenStorage->getToken())) {
        throw new AccessDeniedHttpException('Cannot edit user');
      }
    }
  }

  private static $requiredRoles = [
    Event::class => 'ROLE_EVENT_EDITOR',
    Place::class => 'ROLE_PLACE_EDITOR',
    Group::class => 'ROLE_USER_EDITOR',
    User::class => 'ROLE_USER_EDITOR',
    Tag::class => 'ROLE_TAG_EDITOR',
    UnknownTag::class => 'ROLE_TAG_EDITOR',
    Feed::class => 'ROLE_FEED_EDITOR',
  ];

  private function requireRole($roleName) {
    if (!$this->hasRole($roleName)) {
      throw new AccessDeniedHttpException('Role ' . $roleName . ' required for this action');
    }
  }

  private function hasRole($roleName) {
    $token = $this->tokenStorage->getToken();
    foreach ($this->roleHierarchy->getReachableRoles($token->getRoles()) as $role) {
      if ($roleName === $role->getRole()) {
        return TRUE;
      }
    }

    return FALSE;
  }
}
