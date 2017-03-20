<?php

namespace AdminBundle\Factory;

use AdminBundle\Service\FeedReader\ValueConverter;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineExtensions\Taggable\Taggable;
use FPN\TagBundle\Entity\TagManager;
use AppBundle\Entity\Entity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 *
 */
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

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param \Doctrine\ORM\EntityManagerInterface $em
   * @param \AdminBundle\Service\FeedReader\ValueConverter $valueConverter
   * @param \FPN\TagBundle\Entity\TagManager $tagManager
   */
  public function __construct(ContainerInterface $container, EntityManagerInterface $em, ValueConverter $valueConverter, TagManager $tagManager = NULL) {
    $this->container = $container;
    $this->em = $em;
    $this->valueConverter = $valueConverter;
    $this->tagManager = $tagManager;
    $this->accessor = PropertyAccess::createPropertyAccessor();
  }

  /**
   * @param $entity
   */
  protected function persist($entity) {
    $this->em->persist($entity);
  }

  /**
   *
   */
  protected function flush() {
    $this->em->flush();
  }

  /**
   * @param \AppBundle\Entity\Entity $entity
   * @param array $values
   * @return $this
   */
  protected function setValues(Entity $entity, array $values) {
    foreach ($values as $key => $value) {
      if ($this->valueConverter) {
        $value = $this->valueConverter->convert($value, $key);
      }
      $this->setValue($entity, $key, $value);
    }

    return $this;
  }

  /**
   * @param \AppBundle\Entity\Entity $entity
   * @param $key
   * @param $value
   */
  protected function setValue(Entity $entity, $key, $value) {
    switch ($key) {
      case 'id':
        return;

      case 'tags':
        if ($entity instanceof Taggable && $this->tagManager) {
          $tags = $this->tagManager->loadOrCreateTags($value);
          $this->tagManager->replaceTags($tags, $entity);
        }
        return;
    }

    if ($this->accessor->isWritable($entity, $key)) {
      $this->accessor->setValue($entity, $key, $value);
    }
  }

  protected $user;

  /**
   * @param \AppBundle\Entity\User $user
   */
  public function setUser(User $user) {
    $this->user = $user;
  }

  /**
   *
   */
  protected function getUser() {
    if ($this->user) {
      return $this->user;
    }

    if ($this->container->has('security.token_storage')) {
      $token = $this->container->get('security.token_storage')->getToken();

      return $token ? $token->getUser() : NULL;
    }

    return NULL;
  }
}
