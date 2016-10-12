<?php

namespace AppBundle\Entity;

use AdminBundle\Service\TagNormalizerInterface;
use Doctrine\ORM\EntityManager;
use FPN\TagBundle\Entity\TagManager as BaseTagManager;
use FPN\TagBundle\Util\SlugifierInterface;

class TagManager extends BaseTagManager {
  /**
   * @var TagNormalizerInterface
   */
  private $tagNormalizer;

  public function __construct(EntityManager $em, $tagClass = null, $taggingClass = null, SlugifierInterface $slugifier) {
    parent::__construct($em, $tagClass, $taggingClass, $slugifier);
  }

  public function setTagNormalizer(TagNormalizerInterface $tagNormalizer = null) {
    $this->tagNormalizer = $tagNormalizer;
  }

  /*
   * {@inheritdoc}
   */
  public function loadOrCreateTags(array $names) {
    if ($this->tagNormalizer) {
      $names = $this->tagNormalizer->normalize($names, $this);
    }
    $names = array_filter($names);

    return parent::loadOrCreateTags($names);
  }

  public function loadTags(array $names = null) {
    $builder = $this->em->createQueryBuilder();
    $builder
      ->select('t')
      ->from($this->tagClass, 't');

    if ($names) {
      $builder->where($builder->expr()->in('t.name', $names));
    }

    $tags = $builder
      ->getQuery()
      ->getResult();

    return $tags;
  }

  public function createTag($name)
  {
    return parent::createTag($name);
  }

  public function deleteTag(Tag $tag) {
    // Delete relations to entities.
    $builder = $this->em->createQueryBuilder();
    $builder
      ->delete($this->taggingClass, 't')
      ->where($builder->expr()->eq('t.tag', $tag->getId()))
      ->getQuery()
      ->execute();
    ;

    // Delete tag
    $this->em->remove($tag);
    $this->em->flush();
  }
}
