<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Tests\AppBundle\Test;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @coversNothing
 */
class DatabaseTestCase extends ContainerTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    private static $application;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->em = $this->container->get('doctrine')->getManager();

        self::runCommand('doctrine:database:create', ['--quiet' => true]);
        self::runCommand('doctrine:schema:create', ['--quiet' => true]);
        self::runCommand('doctrine:schema:update', ['--quiet' => true, '--force' => true]);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks

        self::runCommand('doctrine:database:drop', ['--quiet' => true, '--force' => true]);
    }

    protected static function runCommand($command, array $args = [])
    {
        $args['command'] = $command;
        $result = self::getApplication()->run(new ArrayInput($args));

        if (0 !== $result) {
            throw new \Exception('Command '.$command.' failed');
        }
    }

    protected function persist($entity)
    {
        $this->em->persist($entity);

        return $this;
    }

    protected function flush()
    {
        $this->em->flush();

        return $this;
    }

    private static function getApplication()
    {
        if (null === self::$application) {
            self::$application = new Application(self::$kernel);
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }
}
