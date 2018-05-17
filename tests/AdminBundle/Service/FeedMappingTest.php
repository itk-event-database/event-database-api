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
use Tests\AppBundle\Test\ContainerTestCase;

/**
 * @coversNothing
 */
class FeedMappingTest extends ContainerTestCase implements Controller
{
    private $converter;
    private $events;

    public function testMappingBibliotekerne()
    {
        $this->readFeed(preg_replace('/^testMapping/', '', __FUNCTION__));

        $this->assertEquals(10, count($this->events));
        foreach ($this->events as $event) {
            $this->assertEquals(1, count($event['occurrences']));
        }
    }

    public function testMappingSpotFestival()
    {
        $this->readFeed(preg_replace('/^testMapping/', '', __FUNCTION__));

        $this->assertEquals(296, count($this->events));
        $event = $this->events[0];
        $this->assertEquals(['app_festival', 'Music', 'NO', 'UrbanMusicNetwork', 'LineUp'], $event['tags']);
    }

    public function testMappingMusikhusetAarhus()
    {
        $this->readFeed(preg_replace('/^testMapping/', '', __FUNCTION__));

        $this->assertEquals(24, count($this->events));
        $event = $this->events[0];
        $this->assertEquals(['Udstilling', 'Gratis'], $event['tags']);
        $this->assertEquals(3, count($event['occurrences']));
        $occurrence = $event['occurrences'][0];
        $this->assertEquals('Strøget', $occurrence['room']);
        $this->assertEquals(1, count($occurrence['place']));
        $place = $occurrence['place'][0];
        $this->assertEquals('Musikhuset Aarhus', $place['name']);

        $event = $this->events[1];
        $this->assertEquals(1, count($event['occurrences']));
    }

    public function testMappingBilletlugen()
    {
        $this->readFeed(preg_replace('/^testMapping/', '', __FUNCTION__));

        $this->assertEquals(50, count($this->events));

        $event = $this->events[0];
        $this->assertEquals(['Opera'], $event['tags']);
        $this->assertEquals('http://www.billetlugen.dk/images/primary/37548', $event['original_image']);
        $this->assertEquals(1, count($event['occurrences']));
        $occurrence = $event['occurrences'][0];
        $this->assertEquals('Musikhuset Aarhus', $occurrence['venue']);
        $this->assertEquals('Store Sal', $occurrence['room']);
        $this->assertEquals('http://www.billetlugen.dk/referer/?r=266abe1b7fab4562a5c2531d0ae62171&p=/koeb/billetter/37548/71992/', $occurrence['url']);
        // $this->assertEquals(new \DateTime('2016-08-17T19:30:00', new \DateTimeZone('CEST')), $occurrence['startDate']);

        $event = $this->events[5];
        $this->assertEquals(['Show', 'Comedy', 'Stand-Up'], $event['tags']);
        $this->assertEquals(1, count($event['occurrences']));
    }

    public function testMappingAarhus2017()
    {
        $this->readFeed(preg_replace('/^testMapping/', '', __FUNCTION__));

        $this->assertEquals(18, count($this->events));

        $event = $this->events[16];
        $this->assertEquals(['Scenekunst', 'Musik og lyd'], $event['tags']);
        $this->assertEquals(3, count($event['occurrences']));
        $occurrence = $event['occurrences'][0];

        $place = $occurrence['place'];
        $this->assertEquals('Musikhuset Aarhus', $place['name']);
        $this->assertEquals('Thomas Jensens Allé', $place['streetAddress']);
    }

    public function testMappingDokk1()
    {
        $this->readFeed(preg_replace('/^testMapping/', '', __FUNCTION__));

        $this->assertEquals(10, count($this->events));

        $event = $this->events[0];
        $this->assertEquals(1, count($event['occurrences']));
        $occurrence = $event['occurrences'][0];
        $this->assertEquals(new \DateTime('2016-10-10T13:00:00+02:00'), $occurrence['startDate']);
        $place = $occurrence['place'];
        $this->assertEquals('Dokk1', $place['name']);
    }

    public function testMappingEventDatabase()
    {
        $this->readFeed(preg_replace('/^testMapping/', '', __FUNCTION__));

        $this->assertEquals(10, count($this->events));
        $event = $this->events[0];
        $this->assertEquals(1, count($event['occurrences']));
        $occurrence = $event['occurrences'][0];
        $this->assertEquals(new \DateTime('2016-10-10T13:00:00+02:00'), $occurrence['startDate']);
        $place = $occurrence['place'];
        $this->assertEquals('Dokk1', $place['name']);
    }

    public function createEvent(array $data)
    {
        if (isset($data['image'])) {
            $data['original_image'] = $data['image'];
            // $data['image'] = $this->converter->downloadImage($data['image']);
        }

        $this->events[] = $data;
    }

    public function convertValue($value, $name)
    {
        return $this->converter->convert($value, $name);
    }

    private function readFeed(string $name)
    {
        $feedConfiguration = $this->readFixture($name.'.yml');
        $type = $feedConfiguration['type'];
        $data = $this->readFixture($name.'.'.$type);

        $feed = new Feed();
        $feed->setConfiguration($feedConfiguration);
        $this->converter = $this->container->get('value_converter');

        $this->events = [];
        $reader = $this->container->get('feed_reader.'.$type);
        $reader
        ->setController($this)
        ->setFeed($feed);
        $reader->read($data);
    }
}
