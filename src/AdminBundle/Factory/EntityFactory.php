<?php
/**
 * Created by PhpStorm.
 * User: turegjorup
 * Date: 11/08/16
 * Time: 15:57
 */

namespace AdminBundle\Factory;

use AdminBundle\Service\ValueConverter;
use Doctrine\ORM\EntityManagerInterface;
use FPN\TagBundle\Entity\TagManager;
use AppBundle\Entity\Entity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class EntityFactory {
  protected $container;
  protected $em;
  protected $valueConverter;
  protected $tagManager;

  public function __construct(ContainerInterface $container, EntityManagerInterface $em, ValueConverter $valueConverter, TagManager $tagManager = null)  {
    $this->container = $container;
    $this->em = $em;
    $this->valueConverter = $valueConverter;
    $this->tagManager = $tagManager;
  }

  protected function persist($entity) {
    $this->em->persist($entity);
  }

  protected function flush() {
    $this->em->flush();
  }

  protected function setValues(Entity $entity, array $values) {
    $accessor = PropertyAccess::createPropertyAccessor();

    foreach ($values as $key => $value) {
      if ($this->valueConverter) {
        $value = $this->valueConverter->convert($value, $key);
      }
      $this->setValue($entity, $key, $value, $accessor);
    }

    return $this;
  }

  protected function setValue(Entity $entity, $key, $value, PropertyAccessor $accessor) {
    if ($accessor->isWritable($entity, $key)) {
      $accessor->setValue($entity, $key, $value);
    }
  }

}