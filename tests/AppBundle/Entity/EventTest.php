<?php

namespace AppBundle\Entity;

use Tests\AppBundle\Test\DatabaseTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class EventTest extends DatabaseTestCase {

  public function testTags() {
    $tagManager = $this->container->get('fpn_tag.tag_manager');

    $event = new Event();
    $event->setName(__METHOD__);
    $tags = $tagManager->loadOrCreateTags(['a', 'b']);
    $tagManager->addTags($tags, $event);
    $this->em->persist($event);
    $this->em->flush();

    $tagManager->saveTagging($event);

    $sql = 'select * from tag';
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $this->assertEquals(2, count($result));

    $event = $this->em->getRepository(get_class($event))->find($event->getId());
    $tagManager->loadTagging($event);
    $this->assertEquals(2, count($event->getTags()));

    $this->assertEquals(['a', 'b'], $event->getTags()->toArray());

    $sql = 'select * from tagging';
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();

    $anotherEvent = new Event();
    $anotherEvent->setName(__METHOD__);
    $tags = $tagManager->loadOrCreateTags(['b', 'c']);
    $tagManager->addTags($tags, $anotherEvent);
    $this->em->persist($anotherEvent);
    $this->em->flush();

    $sql = 'select * from tag';
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $this->assertEquals(3, count($result));
  }

  public function testOrphanedOccurrences() {
    $username = 'test';
    $password = 'test';
    $firewall = 'main';
    $roles = ['ROLE_ADMIN'];
    $token = new UsernamePasswordToken($username, $password, $firewall, $roles);
    $this->container->get('security.token_storage')->setToken($token);

    $event = new Event();
    $event->setName(__METHOD__);

    $occurrence = new Occurrence();
    $occurrence->setStartDate(new \DateTime());
    $occurrences = new ArrayCollection();
    $occurrences->add($occurrence);
    $event->setOccurrences($occurrences);

    $this->em->persist($event);
    $this->em->flush();

    $this->assertEquals(1, $event->getId());
    $this->assertEquals(1, $occurrence->getId());

    $occurrence = new Occurrence();
    $occurrence->setStartDate(new \DateTime());
    $occurrences = new ArrayCollection();
    $occurrences->add($occurrence);
    $event->setOccurrences($occurrences);

    $this->em->persist($event);
    $this->em->flush();

    $this->assertEquals(1, $event->getId());
    $this->assertEquals(2, $occurrence->getId());

    $sql = 'SELECT * from occurrence WHERE event_id = 1 AND id = 1';
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();

    $this->assertEquals(0, count($result));
  }

}
