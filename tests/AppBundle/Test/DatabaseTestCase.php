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

    self::runCommand('doctrine:database:create', ['--quiet' => TRUE]);
    self::runCommand('doctrine:schema:create', ['--quiet' => TRUE]);
    self::runCommand('doctrine:schema:update', ['--quiet' => TRUE, '--force' => TRUE]);
  }

  /**
   * {@inheritDoc}
   */
  protected function tearDown() {
    parent::tearDown();

    $this->em->close();
    $this->em = NULL; // avoid memory leaks

    self::runCommand('doctrine:database:drop', ['--quiet' => TRUE, '--force' => TRUE]);
  }

  protected static function runCommand($command, array $args = []) {
    $args['command'] = $command;
    $result = self::getApplication()->run(new ArrayInput($args));

    if ($result !== 0) {
      throw new \Exception('Command ' . $command . ' failed');
    }
  }

  private static $application;

  private static function getApplication() {
    if (self::$application === NULL) {
      self::$application = new Application(self::$kernel);
      self::$application->setAutoExit(FALSE);
    }

    return self::$application;
  }

  protected function persist($entity) {
    $this->em->persist($entity);

    return $this;
  }

  protected function flush() {
    $this->em->flush();

    return $this;
  }

}
