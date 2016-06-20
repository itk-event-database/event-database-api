<?php

namespace AdminBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AdminBundle\Entity\Feed;
use Symfony\Component\Yaml\Yaml;

class LoadFeeds extends ContainerAware implements FixtureInterface {
  public function load(ObjectManager $manager) {
    $feedConfigPath = $this->container->get('kernel')->getRootDir() . '/config/feeds.yml';
    $config = Yaml::parse($feedConfigPath);

    $repository = $this->container->get('doctrine')->getRepository('AdminBundle:Feed');

    foreach ($config as $name => $configuration) {
      $feed = $repository->findOneByName($name);
      if (!$feed) {
        $feed = new Feed();
      }
      $feed
        ->setName($name)
        ->setConfiguration($configuration);

      $manager->persist($feed);
      $manager->flush();
    }
  }

}
