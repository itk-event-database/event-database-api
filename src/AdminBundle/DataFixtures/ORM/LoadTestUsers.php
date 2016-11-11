<?php

namespace AdminBundle\DataFixtures\ORM;

use AdminBundle\DataFixtures\ORM\LoadData;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use AppBundle\Entity\User;
use Symfony\Component\Yaml\Yaml;

class LoadTestUsers extends LoadData {
  protected $order = 4;

  public function load(ObjectManager $manager) {
    $yaml = $this->loadFixture('test_users.yml');
    $config = Yaml::parse($yaml);

    $repository = $this->container->get('doctrine')->getRepository('AppBundle:User');

    foreach ($config as $username => $data) {
      $user = $repository->findOneByUsername($username);
      if (!$user) {
        $user = new User();
      }
      $user->setUsername($username)
        ->setEnabled(true)
        ->setPlainPassword($data['password'])
        ->setEmail($data['email'])
        ->setRoles(array($data['role']));

      $manager->persist($user);
      $manager->flush();
    }
  }
}
