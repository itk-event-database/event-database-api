<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\DataFixtures\ORM;

use AdminBundle\Entity\Feed;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class LoadFeeds extends LoadData
{
    protected $order = 3;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $yaml = $this->loadFixture('feeds.yml');
        $config = Yaml::parse($yaml);

        $userRepository = $this->container->get('doctrine')->getRepository('AppBundle:User');
        $repository = $this->container->get('doctrine')->getRepository('AdminBundle:Feed');

        foreach ($config as $name => $configuration) {
            $feed = $repository->findOneByName($name);
            $user = $userRepository->findOneByUsername($configuration['user']);
            unset($configuration['user']);
            if (!$feed) {
                $feed = new Feed();
            }
            $feed
            ->setCreatedBy($user)
            ->setUser($user)
            ->setName($name)
            ->setConfiguration($configuration);

            $manager->persist($feed);
            $manager->flush();
        }
    }
}
