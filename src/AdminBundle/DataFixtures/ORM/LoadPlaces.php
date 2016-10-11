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
      $place->setImage($configuration['promopic']);
      $place->setLangcode("DA");
      $place->setLongitude($configuration['longitude']);
      $place->setLatitude($configuration['latitude']);
      $place->setUrl($configuration['website']);
      $place->setTelephone($configuration['phone']);
      $place->setLogo($configuration['logo']);
      $place->setEmail($configuration['email']);

      //Override Doctrine id genteration to maintain id's form import

      $place->setId($configuration['place_id']);

      $em = $this->container->get('doctrine')->getManager();
      $metadata = $em->getClassMetaData(get_class($place));
      $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
      $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());

      $manager->persist($place);
    }

    $manager->flush();
  }

}
