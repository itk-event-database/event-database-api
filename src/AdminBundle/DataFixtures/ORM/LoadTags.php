<?php

namespace AdminBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Yaml\Yaml;

/**
 *
 */
class LoadTags extends LoadData {
  protected $order = 5;

  /**
   * @param \Doctrine\Common\Persistence\ObjectManager $manager
   */
  public function load(ObjectManager $manager) {
    $yaml = $this->loadFixture('tags.yml');
    $config = Yaml::parse($yaml);

    $tagManager = $this->container->get('tag_manager');
    $tagManager->setTagNormalizer(NULL);
    $names = $config['tags'];
    $tags = $tagManager->loadOrCreateTags($names);
    $knownTags = [];
    foreach ($tags as $tag) {
      $knownTags[$tag->getName()] = $tag;
    }

    echo 'Tags loaded (' . count($tags) . '):', PHP_EOL;
    foreach ($tags as $tag) {
      echo sprintf('% 3d: %s', $tag->getId(), $tag->getName()), PHP_EOL;
    }

    $unknownTagManager = $this->container->get('unknown_tag_manager');
    $names = array_keys($config['unknown_tags']);
    $tags = $unknownTagManager->loadOrCreateTags($names);
    $unknownTags = [];
    foreach ($tags as $tag) {
      $unknownTags[$tag->getName()] = $tag;
    }

    echo 'Tags loaded (' . count($tags) . '):', PHP_EOL;
    foreach ($tags as $tag) {
      echo sprintf('% 3d: %s', $tag->getId(), $tag->getName()), PHP_EOL;
    }

    // Connect tags to unknown tags.
    $em = $this->container->get('doctrine.orm.default_entity_manager');
    foreach ($config['unknown_tags'] as $unknownName => $name) {
      if (!$name) {
        continue;
      }
      $unknownTag = $unknownTagManager->loadTags([$unknownName])[0];
      $knownTag = $tagManager->loadTags([$name])[0];
      $unknownTag->setTag($knownTag);
      $em->persist($unknownTag);
      $em->flush();
    }
  }

}
