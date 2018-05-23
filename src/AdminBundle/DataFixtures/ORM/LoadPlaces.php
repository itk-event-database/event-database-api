<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\DataFixtures\ORM;

use AppBundle\Entity\Place;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Yaml\Yaml;

class LoadPlaces extends LoadData
{
    protected $order = 6;

    public function load(ObjectManager $manager)
    {
        $yaml = $this->loadFixture('places.yml');
        $config = Yaml::parse($yaml);

        $userRepository = $this->container->get('doctrine')->getRepository('AppBundle:User');
        $user = $userRepository->findOneByUsername('api-admin');

        $repository = $this->container->get('doctrine')->getRepository('AppBundle:Place');

        $places_count = count($config['data']);
        $loop = 0;

        echo 'Places loaded ('.$places_count.'):', PHP_EOL;

        foreach ($config['data'] as $name => $configuration) {
            $name = trim($configuration['name']);
            $city = $configuration['city'];
            $city = str_replace('Århus', 'Aarhus', $city);

            $place = $repository->findOneById($configuration['place_id']);

            if (!$place) {
                $place = new Place();
            }

            $place->setCreatedBy($user);
            $place->setUpdatedBy($user);

            if (!empty($name)) {
                $place->setName($name);
            }
            if (!empty($configuration['adress'])) {
                $place->setStreetAddress($configuration['adress']);
            }
            if (!empty($configuration['postcode'])) {
                $place->setPostalCode($configuration['postcode']);
            }
            if (!empty($city)) {
                $place->setAddressLocality($city);
            }
            if (!empty($configuration['description'])) {
                $place->setDescription($configuration['description']);
            }
            if (!empty($configuration['promopic'])) {
                $place->setImage($configuration['promopic']);
            }
            $place->setLangcode('DA');
            if (!empty($configuration['longitude'])) {
                $place->setLongitude($configuration['longitude']);
            }
            if (!empty($configuration['latitude'])) {
                $place->setLatitude($configuration['latitude']);
            }
            if (!empty($configuration['website'])) {
                $place->setUrl($configuration['website']);
            }
            if (!empty($configuration['phone'])) {
                $place->setTelephone($configuration['phone']);
            }
            if (!empty($configuration['logo'])) {
                $place->setLogo($configuration['logo']);
            }
            if (!empty($configuration['email'])) {
                $place->setEmail($configuration['email']);
            }

            //Override Doctrine id generation to maintain id's form import

            $place->setId($configuration['place_id']);

            $em = $this->container->get('doctrine')->getManager();
            $metadata = $em->getClassMetaData(get_class($place));
            $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new AssignedGenerator());

            $manager->persist($place);

            if (0 === $loop % 100) {
                echo 'Completed '.$loop.' / '.$places_count.' places', PHP_EOL;
            }

            ++$loop;
        }

        echo 'Completed '.$loop.' / '.$places_count.' places', PHP_EOL;
        echo 'Flushing to DB...', PHP_EOL;

        $manager->flush();
    }
}
