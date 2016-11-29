<?php

namespace AdminBundle\DataFixtures\ORM;

use AppBundle\Entity\Place;
use AppBundle\Entity\Tag;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Yaml\Yaml;

class LoadPlaceCategories extends LoadData
{
  protected $order = 7;

  public function load(ObjectManager $manager)
  {
    $yaml = $this->loadFixture('places.yml');
    $config_places = Yaml::parse($yaml);

    $tagManager = $this->container->get('fpn_tag.tag_manager');
    $yaml = $this->loadFixture('categories.yml');
    $config_categories = Yaml::parse($yaml);
    $categories = array();

    $places_count = count($config_places['data']);

    echo 'Adding '. count($config_categories['data']). ' categories to ' . $places_count . ' places', PHP_EOL;

    foreach ($config_categories['data'] as $name => $configuration) {
      $categories[$configuration['id']] = $tagManager->loadOrCreateTag($configuration['name']);
    }

    $repository = $this->container->get('doctrine')->getRepository('AppBundle:Place');

    $loop = 0;
    foreach ($config_places['data'] as $name => $configuration) {
      $place = $repository->findOneById($configuration['place_id']);

      if($place) {
        foreach ($configuration['category_ids'] as $category_id) {
          $tagManager->addTag($categories[$category_id], $place);
        }
      }

      $tagManager->saveTagging($place);

      if($loop % 100 == 0) {
        echo 'Completed '. $loop. ' / ' . $places_count . ' places', PHP_EOL;
      }

      $loop++;
    }
    echo 'Completed '. $loop. ' / ' . $places_count . ' places', PHP_EOL;
    echo 'Done adding tags to places', PHP_EOL;

  }

}
