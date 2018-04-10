<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Tests\AppBundle\Test;

use Symfony\Component\Yaml\Yaml;

/**
 * @coversNothing
 */
class ContainerTestCase extends BaseTestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        self::bootKernel();
        $this->container = static::$kernel->getContainer();
    }

    /**
     * Read a fixture file and convert the content into soemthing useful.
     *
     * @param mixed      $filename
     * @param null|mixed $type
     */
    protected function readFixture($filename, $type = null)
    {
        $path = $this->getFixturePath($filename);
        $content = file_get_contents($path);
        $info = pathinfo($path);

        if (null === $type) {
            $type = $info['extension'];
        }

        switch ($type) {
            case 'yml':
            case 'yaml':
                $content = YAML::parse($content);

                break;
            case 'json':
                $content = json_decode($content, true);

                break;
            case 'xml':
                $content = new \SimpleXmlElement($content);

                break;
        }

        return $content;
    }

    /**
     * Get fixture path from filename and current test class name.
     *
     * The path is computed from the current test class name like this:
     *
     * AdminBundle\Service\FeedReaderTest (tests/AdminBundle/Service/FeedReaderTest.php) ⟼
     * tests/fixtures/AdminBundle/Service/FeedReaderTest/)
     */
    protected function getFixturePath(string $filename = '')
    {
        $filepath = $this->container->get('kernel')->getRootDir().'/../tests/fixtures/'.str_replace('\\', '/', get_class($this)).'/'.$filename;

        if (!file_exists($filepath)) {
            throw new \Exception('Fixture '.$filename.' ('.$filepath.') not found.');
        }

        return $filepath;
    }
}
