<?php

namespace AdminBundle\DataFixtures\ORM;

use AppBundle\Entity\Place;
use AppBundle\Entity\Tag;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Yaml\Yaml;

class LoadPlaces extends LoadData
{
  protected $order = 2;

  public function load(ObjectManager $manager)
  {
    $yaml = $this->loadFixture('places.yml');
    $config = Yaml::parse($yaml);

    $tagManager = $this->container->get('fpn_tag.tag_manager');

    $repository = $this->container->get('doctrine')->getRepository('AppBundle:Place');

    foreach ($config['data'] as $name => $configuration) {
      $name = trim($configuration['name']);
      $city = $configuration['city'];
      $city = str_replace('Århus', 'Aarhus', $city);

      $place = $repository->findOneByName($name);

      if(!$place) {
        $place = new Place();
      }

      $place->setName($name);
      $place->setStreetAddress($configuration['adress']);
      $place->setPostalCode($configuration['postcode']);
      $place->setAddressLocality($city);
      $place->setDescription($configuration['description']);

      $manager->persist($place);
    }

    $manager->flush();
  }

}
