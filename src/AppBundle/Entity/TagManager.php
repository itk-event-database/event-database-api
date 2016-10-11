<?php

namespace AppBundle\Entity;

use AdminBundle\Service\TagNormalizerInterface;
use Doctrine\ORM\EntityManager;
use FPN\TagBundle\Entity\TagManager as MyBaseTagManager;
use FPN\TagBundle\Util\SlugifierInterface;

class TagManager extends MyBaseTagManager {
  /**
   * @var TagNormalizerInterface
   */
  private $tagNormalizer;

  public function __construct(EntityManager $em, $tagClass = null, $taggingClass = null, SlugifierInterface $slugifier, TagNormalizerInterface $tagNormalizer) {
    parent::__construct($em, $tagClass, $taggingClass, $slugifier);
    $this->tagNormalizer = $tagNormalizer;
  }

  public function loadOrCreateTags(array $names) {
    $names = array_filter(array_map(function ($name) {
      return $this->tagNormalizer->normalize($name);
    }, $names));

    return parent::loadOrCreateTags($names);
  }
}
