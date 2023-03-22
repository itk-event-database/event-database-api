<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service;

use AdminBundle\Entity\Feed;
use AdminBundle\Service\FeedReader\Controller;
use AdminBundle\Service\FeedReader\ValueConverter;
use Tests\AppBundle\Test\ContainerTestCase;

/**
 * @coversNothing
 */
class FeedReaderTimeZoneTest extends ContainerTestCase implements Controller
{
    /**
     * @var ValueConverter
     */
    private $converter;

    /**
     * @var FileHandler
     */
    private $fileHandler;

    private $events = [];

    private static $timeZoneNames = [
        'America/Indianapolis',
        'Asia/Calcutta',
        'Atlantic/Faeroe',
        'Australia/NSW',
        'CET',
        'Pacific/Samoa',
        'US/Pacific',
        'UTC',
    ];

    public function testReadFeed()
    {
        $feedConfiguration = $this->readFixture('feed.config.yml');

        $utcTimeZone = new \DateTimeZone('UTC');
        foreach (self::$timeZoneNames as $timeZoneName) {
            $this->events = [];
            $feedConfiguration['timeZone'] = $timeZoneName;
            $timeZone = new \DateTimeZone($feedConfiguration['timeZone']);

            $json = $this->readFixture('feed.data.yml');
            $feed = $this->createFeed($feedConfiguration);
            $reader = $this->container->get('feed_reader.json');
            $reader
            ->setController($this)
            ->setFeed($feed);
            $reader->read($json);

            $this->assertEquals(1, count($this->events));
            $event = $this->events[0];
            $this->assertEquals(2, count($event['occurrences']));
            $occurrence = $event['occurrences'][0];
            $this->assertEquals(new \DateTime('2001-01-01T08:00:00', $timeZone), $occurrence['startDate'], $timeZoneName);
            $this->assertEquals(new \DateTime('2001-01-01T12:00:00', $timeZone), $occurrence['endDate'], $timeZoneName);
            $occurrence = $event['occurrences'][1];
            $this->assertEquals(new \DateTime('2001-01-01T08:00:00+01:00'), $occurrence['startDate'], $timeZoneName);
            $this->assertEquals(new \DateTime('2001-01-01T12:00:00+01:00'), $occurrence['endDate'], $timeZoneName);
        }
    }

    public function createEvent(array $data)
    {
        if (isset($data['image'])) {
            $data['original_image'] = $data['image'];
            $data['image'] = $this->fileHandler->download($data['image']);
        }

        $this->events[] = $data;
    }

    public function convertValue($value, $name)
    {
        return $this->converter->convert($value, $name);
    }

    private function createFeed(array $configuration)
    {
        $feed = new Feed();
        $feed->setConfiguration($configuration);
        $this->converter = $this->container->get('value_converter');
        $this->converter->setFeed($feed);
        $this->fileHandler = $this->container->get('file_handler');

        return $feed;
    }
}
