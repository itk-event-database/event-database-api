<?php

namespace AdminBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\User;
use Symfony\Component\Yaml\Yaml;

class LoadFeedUsers extends LoadData {
  public function load(ObjectManager $manager) {
    $yaml = $this->loadFixture('feed_users.yml');
    $config = Yaml::parse($yaml);

    $repository = $this->container->get('doctrine')->getRepository('AppBundle:User');

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
          $user->{'set' . $key}($value);
        }
      }

      $manager->persist($user);
      $manager->flush();
    }
  }
}
