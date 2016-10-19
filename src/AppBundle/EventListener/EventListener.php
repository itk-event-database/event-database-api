<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\TagManager;
use AppBundle\Entity\Thing;
use AppBundle\Job\DownloadFilesJob;
use AppBundle\Security\Authorization\Voter\EventVoter;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use DoctrineExtensions\Taggable\Taggable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\Event;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 *
 */
class EventListener {
  /**
   * @var ContainerInterface
   */
  private $container;

  /**
   * @var TagManager
   */
  private $tagManager;

  /**
   *
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
    $this->tagManager = $container->get('tag_manager');
  }

  /**
   *
   */
  public function preUpdate(LifecycleEventArgs $args) {
    $object = $args->getObject();

    if ($object instanceof Event) {
      if (!$this->isGranted(EventVoter::UPDATE, $object)) {
        throw new AccessDeniedHttpException('Access denied');
      }
    }
  }

  /**
   *
   */
  public function preRemove(LifecycleEventArgs $args) {
    $object = $args->getObject();

    if ($object instanceof Event) {
      if (!$this->isGranted(EventVoter::REMOVE, $object)) {
        throw new AccessDeniedHttpException('Access denied');
      }
    }
  }

  /**
   *
   */
  public function prePersist(LifecycleEventArgs $args) {
    $object = $args->getObject();
    if ($object instanceof Thing) {
      if ($this->container->has('description_normalizer')) {
        $description = $object->getDescription();
        $description = $this->container->get('description_normalizer')
                ->normalize($description);
        $object->setDescription($description);
      }
    }
    if ($object instanceof Event) {
      if ($this->container->has('excerpt_normalizer')) {
        $excerpt = $object->getExcerpt() ?: $object->getDescription();
        $excerpt = $this->container->get('excerpt_normalizer')->normalize($excerpt);
        $object->setExcerpt($excerpt);
      }
    }
  }

  /**
   *
   */
  public function postPersist(LifecycleEventArgs $args) {
    $object = $args->getObject();
    if ($object instanceof Taggable) {
      $this->tagManager->saveTagging($object);
    }

    if ($object instanceof Thing) {
      $job = new DownloadFilesJob();
      $job->args = [
        'className' => get_class($object),
        'id' => $object->getId(),
        'fields' => ['image'],
      ];

      $this->container->get('resque')->enqueue($job);
    }
  }

  /**
   * Check that the requested Event is owned by the current user.
   *
   * @throws AccessDeniedHttpException
   */
  private function checkOwner(Event $event) {
    $token = $this->container->get('security.token_storage')->getToken();
    $user = $token ? $token->getUser() : NULL;

    if ($token->getRoles()) {
    }

    if (!$user || !$event->getCreatedBy() || $user->getId() != $event->getCreatedBy()->getId()) {
      throw new AccessDeniedHttpException('Access denied');
    }
  }

  /**
   * Checks if the attributes are granted against the current authentication token and optionally supplied object.
   *
   * @param mixed $attributes
   *   The attributes
   * @param mixed $object
   *   The object
   *
   * @return bool
   *
   * @throws \LogicException
   */
  protected function isGranted($attributes, $object = NULL) {
    if (!$this->container->has('security.authorization_checker')) {
      throw new \LogicException('The SecurityBundle is not registered in your application.');
    }

    return $this->container->get('security.authorization_checker')->isGranted($attributes, $object);
  }

}
