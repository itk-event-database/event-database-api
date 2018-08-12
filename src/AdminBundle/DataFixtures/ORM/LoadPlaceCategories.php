<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
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
        $categories = [];

        $places_count = count($config_places['data']);

        echo 'Adding '.count($config_categories['data']).' categories to '.$places_count.' places', PHP_EOL;

        foreach ($config_categories['data'] as $name => $configuration) {
            $categories[$configuration['id']] = $tagManager->loadOrCreateTag($configuration['name']);
        }

        $repository = $this->container->get('doctrine')->getRepository('AppBundle:Place');

        $loop = 0;
        foreach ($config_places['data'] as $name => $configuration) {
            $place = $repository->findOneById($configuration['place_id']);

            if ($place) {
                foreach ($configuration['category_ids'] as $category_id) {
                    $tagManager->addTag($categories[$category_id], $place);
                }
            }

            $tagManager->saveTagging($place);

            if (0 === $loop % 100) {
                echo 'Completed '.$loop.' / '.$places_count.' places', PHP_EOL;
            }

            ++$loop;
        }
        echo 'Completed '.$loop.' / '.$places_count.' places', PHP_EOL;
        echo 'Done adding tags to places', PHP_EOL;
    }
}
