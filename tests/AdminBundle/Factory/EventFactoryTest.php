<?php

namespace AdminBundle\Factory;

use AdminBundle\Entity\Feed;
use AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\AppBundle\Test\DatabaseTestCase;

class EventFactoryTest extends DatabaseTestCase
{

    public function test()
    {
        $this->authenticate($this->container);

        $feed = new Feed();
        $feed
        ->setName(__FUNCTION__)
        ->setConfiguration([]);
        $this->persist($feed)->flush();

        $factory = $this->container->get('event_factory');
        $factory->setFeed($feed);

        $data = [
        'name' => 'Some event',
        'feed_event_id' => uniqid(),
        'occurrences' => [
        [
          'startDate' => new \DateTime('2000-01-01'),
          'endDate' => new \DateTime('2000-01-01'),
          'room' => 'Some room',
          'place' => [
            'name' => 'Some place',
          ],
        ],
        [
          'startDate' => new \DateTime('2000-01-01'),
          'endDate' => new \DateTime('2000-01-01'),
          'room' => 'Some room',
          'place' => [
            'name' => 'Some place',
          ],
        ],
        ],
        ];
        $event = $factory->get($data);

        $sql = 'select * from event';
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result));

        $sql = 'select * from occurrence';
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $this->assertEquals(2, count($result));

        $sql = 'select * from place';
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result));
    }

    private function authenticate(ContainerInterface $container)
    {
        $username = 'username';
        $email = $username . '@example.com';
        $password = 'password';
        $firewall = 'main';
        $roles = ['ROLE_ADMIN'];

        $user = new User();
        $user
        ->setUsername($username)
        ->setPlainPassword($password)
        ->setEmail($email)
        ->setRoles($roles)
        ->setEnabled(true);
        $this->persist($user);
        $this->flush();

        $token = new UsernamePasswordToken($user, $password, $firewall);
        $container->get('security.token_storage')->setToken($token);
    }
}
