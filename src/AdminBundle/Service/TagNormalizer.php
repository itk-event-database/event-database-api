<?php

namespace AdminBundle\Service;

use AppBundle\Entity\TagManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class TagNormalizer implements TagNormalizerInterface {
  /**
   * @var ContainerInterface
   */
  private $container;

  /**
   * @var array
   */
  private $configuration;

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   */
  public function __construct(ContainerInterface $container, array $configuration) {
    $this->container = $container;
    $this->configuration = $configuration;
  }

  /**
   * @param array $names
   * @return array
   */
  public function normalize(array $names) {
    $tagManager = $this->getTagManager();
    $tags = $tagManager->loadTags($names);

    $validNames = array_map(function ($tag) {
      return $tag->getName();
    }, $tags);

    $unknownNames = array_udiff($names, $validNames, 'strcasecmp');
    if ($unknownNames) {
      $unknownTags = $this->getUnknownTagManager()->loadOrCreateTags($unknownNames);
      foreach ($unknownTags as $unknownTag) {
        $tag = $unknownTag->getTag();
        if ($tag) {
          $validNames[] = $tag->getName();
        }
      }
    }

    return array_unique($validNames);
  }

  /**
   * @return TagManager
   */
  private function getTagManager() {
    return $this->container->get($this->configuration['services']['tag_manager']);
  }

  /**
   * @return TagManager
   */
  private function getUnknownTagManager() {
    return $this->container->get($this->configuration['services']['unknown_tag_manager']);
  }

}
