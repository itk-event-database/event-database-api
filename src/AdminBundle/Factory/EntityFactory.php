<?php

namespace AdminBundle\Factory;

use AdminBundle\Service\FeedReader\ValueConverter;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineExtensions\Taggable\Taggable;
use FPN\TagBundle\Entity\TagManager;
use AppBundle\Entity\Entity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class EntityFactory {
  /**
   * @var ContainerInterface
   */
  protected $container;

  /**
   * @var EntityManagerInterface
   */
  protected $em;

  /**
   * @var ValueConverter
   */
  protected $valueConverter;

  /**
   * @var TagManager
   */
  protected $tagManager;

  /**
   * @var \Symfony\Component\PropertyAccess\PropertyAccessor
   */
  protected $accessor;

  public function __construct(ContainerInterface $container, EntityManagerInterface $em, ValueConverter $valueConverter, TagManager $tagManager = null)  {
    $this->container = $container;
    $this->em = $em;
    $this->valueConverter = $valueConverter;
    $this->tagManager = $tagManager;
    $this->accessor = PropertyAccess::createPropertyAccessor();
  }

  protected function persist($entity) {
    $this->em->persist($entity);
  }

  protected function flush() {
    $this->em->flush();
  }

  protected function setValues(Entity $entity, array $values) {
    foreach ($values as $key => $value) {
      if ($this->valueConverter) {
        $value = $this->valueConverter->convert($value, $key);
      }
      $this->setValue($entity, $key, $value);
    }

    return $this;
  }

  protected function setValue(Entity $entity, $key, $value) {
    switch ($key) {
      case 'id':
        return;
      case 'tags':
        if ($entity instanceof Taggable && $this->tagManager) {
          $tags = $this->tagManager->loadOrCreateTags($value);
          $this->tagManager->addTags($tags, $entity);
        }
        return;
    }

    if ($this->accessor->isWritable($entity, $key)) {
      $this->accessor->setValue($entity, $key, $value);
    }
  }

}
