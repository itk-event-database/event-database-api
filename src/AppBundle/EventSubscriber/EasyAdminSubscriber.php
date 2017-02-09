<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Entity\Tag;
use AppBundle\Entity\TagManager;
use DoctrineExtensions\Taggable\Taggable;
use FPN\TagBundle\Util\SlugifierInterface;
use JavierEguiluz\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class EasyAdminSubscriber implements EventSubscriberInterface {
  private $tagManager;
  private $slugifier;

  public function __construct(TagManager $tagManager, SlugifierInterface $slugifier) {
    $this->tagManager = $tagManager;
    $this->slugifier = $slugifier;
  }

  public static function getSubscribedEvents() {
    return [
      EasyAdminEvents::PRE_PERSIST => ['setSlug'],
      EasyAdminEvents::POST_PERSIST => ['saveTags'],
      EasyAdminEvents::POST_UPDATE => ['saveTags'],
    ];
  }

  public function setSlug(GenericEvent $event) {
    $entity = $event->getSubject();

    if ($entity instanceof Tag) {
      //$this->tagManager->loadOrCreateTag($entity->getName());
    }

    if (method_exists($entity, 'getName') && method_exists($entity, 'setSlug')) {
      $entity->setSlug($this->slugifier->slugify($entity->getName()));
    }
  }

  public function saveTags(GenericEvent $event) {
    file_put_contents('/tmp/tmp.log', __METHOD__ .' '. PHP_EOL, FILE_APPEND);
    $entity = $event->getSubject();
    if ($entity instanceof Taggable) {
      $this->tagManager->saveTagging($entity);
    }
  }
}
