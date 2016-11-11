<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\TagManager;
use AppBundle\Entity\Thing;
use AppBundle\Job\DownloadFilesJob;
use AppBundle\Security\Authorization\Voter\EditVoter;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use DoctrineExtensions\Taggable\Taggable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\Event;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 *
 */
class EventListener extends EditListener {
  /**
   * @var TagManager
   */
  protected $tagManager;

  public function __construct(ContainerInterface $container) {
    parent::__construct($container);
    $this->tagManager = $this->container->get('tag_manager');
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
}
