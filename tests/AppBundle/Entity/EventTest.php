<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class EventTest extends KernelTestCase
{
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

    $this->assertEquals(1, count($result));
    $this->assertNotNull($result[0]['deleted_at']);
    $this->assertLessThanOrEqual(new \DateTime(), $result[0]['deleted_at']);
  }

  /**
   * @var \Symfony\Component\DependencyInjection\Container
   */
  private $container;

  /**
   * @var \Doctrine\ORM\EntityManager
   */
  private $em;

  /**
   * {@inheritDoc}
   */
  protected function setUp()
  {
    self::bootKernel();

    $this->container = static::$kernel->getContainer();
    $this->em = $this->container->get('doctrine')->getManager();

    self::runCommand('doctrine:database:create');
    self::runCommand('doctrine:schema:create');
    self::runCommand('doctrine:schema:update', ['--force' => true]);
  }

  /**
   * {@inheritDoc}
   */
  protected function tearDown()
  {
    parent::tearDown();

    $this->em->close();
    $this->em = null; // avoid memory leaks

    self::runCommand('doctrine:database:drop', ['--force' => true]);
  }

  protected static function runCommand($command, array $args = [])
  {
    $args['command'] = $command;
    $result = self::getApplication()->run(new ArrayInput($args));

    if ($result !== 0) {
      throw new \Exception('Command '.$command.' failed');
    }
  }

  private static $application;

  private static function getApplication() {
    if (static::$application === null) {
      static::$application = new Application(static::$kernel);
      static::$application->setAutoExit(false);
    }

    return static::$application;
  }
}
