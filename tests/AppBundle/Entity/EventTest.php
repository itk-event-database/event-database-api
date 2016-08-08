<?php

namespace Tests\AppBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;

class EventTest extends KernelTestCase
{
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
