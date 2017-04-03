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
      EasyAdminEvents::POST_PERSIST => [['saveTags'], ['postPersist']],
      EasyAdminEvents::POST_UPDATE => [['saveTags'], ['postUpdate']],
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

  public function postPersist(GenericEvent $event) {
    $subject = $event->getSubject();
    $message = $this->translate('%entity_name% created', ['%entity_name%' => $this->translate($this->getEntityName($subject))]);
    $this->addFlash('info', $message);
  }

  public function postUpdate(GenericEvent $event) {
    $subject = $event->getSubject();
    $message = $this->translate('%entity_name% updated', ['%entity_name%' => $this->translate($this->getEntityName($subject))]);
    $this->addFlash('info', $message);
  }

  private function translate($text) {
    $translator = $this->container->get('translator');
    return $translator->trans($text);
  }

  private function getEntityName($entity) {
    $name = get_class($entity);
    return preg_replace('/^([a-z]+\\\\)+/i', '', $name);
  }

  private function addFlash($type, $message) {
    // $this->container->get('session')->getFlashBag()->add('info', $message);
  }

  public function setSlug(GenericEvent $event) {
    $entity = $event->getSubject();

    if (method_exists($entity, 'getName') && method_exists($entity, 'setSlug')) {
      $entity->setSlug($this->slugifier->slugify($entity->getName()));
    }
  }

  public function saveTags(GenericEvent $event) {
    $entity = $event->getSubject();
    if ($entity instanceof Taggable) {
      $this->tagManager->saveTagging($entity);
    }
  }

}
