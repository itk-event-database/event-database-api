<?php

namespace AdminBundle\DataFixtures\ORM;

use AdminBundle\DataFixtures\ORM\LoadData;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Entity\User;
use Symfony\Component\Yaml\Yaml;

class LoadUsers extends LoadData {
  public function load(ObjectManager $manager) {
    $yaml = $this->loadFixture('users.yml');
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
