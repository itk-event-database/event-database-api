<?php

namespace Tests\AppBundle\Test;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class DatabaseTestCase extends ContainerTestCase {
  /**
   * @var \Doctrine\ORM\EntityManager
   */
  protected $em;

  /**
   * {@inheritDoc}
   */
  protected function setUp() {
    parent::setUp();

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
    if (self::$application === null) {
      self::$application = new Application(self::$kernel);
      self::$application->setAutoExit(false);
    }

    return self::$application;
  }
}
