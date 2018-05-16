<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Yaml\Yaml;

class LoadUsers extends LoadData
{
    protected $order = 2;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $yaml = $this->loadFixture('users.yml');
        $config = Yaml::parse($yaml);

        $repository = $this->container->get('doctrine')->getRepository('AppBundle:User');

        $accessor = new PropertyAccessor();
        foreach ($config as $username => $data) {
            $user = $repository->findOneByUsername($username);
            if (!$user) {
                $user = new User();
            }
            $user->setUsername($username)
            ->setEnabled(true)
            ->setPlainPassword('password')
            ->setRoles(['ROLE_API_WRITE']);
            if ($data) {
                foreach ($data as $key => $value) {
                    if ('groups' === $key) {
                        $groups = $this->container->get('doctrine')->getRepository('AppBundle:Group')->findByName($value);
                        foreach ($groups as $group) {
                            $user->addGroup($group);
                        }
                    } elseif ($accessor->isWritable($user, $key)) {
                        $accessor->setValue($user, $key, $value);
                    }
                }
            }

            $manager->persist($user);
            $manager->flush();
        }
    }
}
