<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\DataFixtures\ORM;

use AppBundle\Entity\Group;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Yaml\Yaml;

class LoadGroups extends LoadData
{
    protected $order = 1;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $yaml = $this->loadFixture('groups.yml');
        $config = Yaml::parse($yaml);

        $repository = $this->container->get('doctrine')->getRepository('AppBundle:Group');

        $accessor = new PropertyAccessor();
        foreach ($config as $name => $data) {
            $group = $repository->findOneByName($name);
            if (!$group) {
                $group = new Group($name);
            }
            $group->setName($name);
            if ($data) {
                foreach ($data as $key => $value) {
                    $accessor->setValue($group, $key, $value);
                }
            }

            $manager->persist($group);
            $manager->flush();
        }
    }
}
