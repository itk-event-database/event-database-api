<?php

namespace AdminBundle\EventSubscriber;

use AppBundle\Entity\TagManager;
use DoctrineExtensions\Taggable\Taggable;
use FPN\TagBundle\Util\SlugifierInterface;
use JavierEguiluz\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class EasyAdminSubscriber implements EventSubscriberInterface {
  private $tagManager;
  private $slugifier;
  private $container;

  public function __construct(TagManager $tagManager, SlugifierInterface $slugifier, ContainerInterface $container) {
    $this->tagManager = $tagManager;
    $this->slugifier = $slugifier;
    $this->container = $container;
  }

  public static function getSubscribedEvents() {
    return [
      EasyAdminEvents::POST_INITIALIZE => ['addQueryParameters'],
      EasyAdminEvents::PRE_PERSIST => ['setSlug'],
    ];
  }

  public function addQueryParameters(GenericEvent $event) {
    $request = $event->getArgument('request');
    if ($request) {
      $action = $request->get('action', 'list');
      $entity = $event->getArgument('entity');
      if (isset($entity[$action]['params'])) {
        foreach ($entity[$action]['params'] as $name => $value) {
          if (!$request->query->has($name)) {
            $request->query->add([$name => $value]);
          }
        }
      }
    }
  }

  public function setSlug(GenericEvent $event) {
    $entity = $event->getSubject();

    if (method_exists($entity, 'getName') && method_exists($entity, 'setSlug')) {
      $entity->setSlug($this->slugifier->slugify($entity->getName()));
    }
  }

}
