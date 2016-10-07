<?php

namespace AdminBundle\DataFixtures\ORM;

use AppBundle\Entity\Tag;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Yaml\Yaml;

class LoadCategories extends LoadData
{
  protected $order = 2;

  public function load(ObjectManager $manager)
  {
    $yaml = $this->loadFixture('categories.yml');
    $config = Yaml::parse($yaml);

    $tags = array();
    $tagManager = $this->container->get('fpn_tag.tag_manager');

    foreach ($config['data'] as $name => $configuration) {
      $name = trim($configuration['name']);
      $tags[] = $tagManager->loadOrCreateTag($name);
    }

    $manager->flush();
  }

}
