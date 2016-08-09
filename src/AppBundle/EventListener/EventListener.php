<?php

namespace AppBundle\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use DoctrineExtensions\Taggable\Taggable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\Event;
use Dunglas\ApiBundle\Event\DataEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EventListener {
  protected $container;

  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  public function onPreUpdate(DataEvent $event) {
    $object = $event->getData();

    if ($object instanceof Event) {
      if (!$this->isGranted('edit', $object)) {
        throw new AccessDeniedHttpException('Access denied');
      }
    }
  }

  public function onPreDelete(DataEvent $event) {
    $object = $event->getData();

    if ($object instanceof Event) {
      if (!$this->isGranted('delete', $object)) {
        throw new AccessDeniedHttpException('Access denied');
      }
    }
  }

  public function postPersist(LifecycleEventArgs $args) {
    $object = $args->getObject();
    if ($object instanceof Taggable) {
      $tagManager = $this->container->get('fpn_tag.tag_manager');
      $tagManager->saveTagging($object);
    }
  }

  /**
   * Check that the requested Event is owned by the current user.
   *
   * @throws AccessDeniedHttpException
   */
  private function checkOwner(Event $event) {
    $token = $this->container->get('security.context')->getToken();
    $user = $token ? $token->getUser() : null;

    if (!$user || !$event->getCreatedBy() || $user->getId() != $event->getCreatedBy()->getId()) {
      throw new AccessDeniedHttpException('Access denied');
    }
  }

  /**
   * Checks if the attributes are granted against the current authentication token and optionally supplied object.
   *
   * @param mixed $attributes The attributes
   * @param mixed $object     The object
   *
   * @return bool
   *
   * @throws \LogicException
   */
  protected function isGranted($attributes, $object = null) {
    if (!$this->container->has('security.authorization_checker')) {
      throw new \LogicException('The SecurityBundle is not registered in your application.');
    }

    return $this->container->get('security.authorization_checker')->isGranted($attributes, $object);
  }
}
