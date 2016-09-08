<?php

namespace AdminBundle\Service;

use Tests\AppBundle\Test\ContainerTestCase;

use AdminBundle\Entity\Feed;
use AdminBundle\Service\FeedReader\Controller;
use AdminBundle\Service\FeedReader\ValueConverter;

class FeedMappingTest extends ContainerTestCase implements Controller {
  private $converter;
  private $events;

  public function testMappingBibliotekerne() {
    $this->readFeed(preg_replace('/^testMapping/', '', __FUNCTION__));

    $this->assertEquals(10, count($this->events));
    foreach ($this->events as $event) {
      $this->assertEquals(1, count($event['occurrences']));
    }
  }

  public function testMappingSPOTFestival() {
    $this->readFeed(preg_replace('/^testMapping/', '', __FUNCTION__));
  }

  public function testMappingMusikhusetAarhus() {
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

  public function testMappingLivejazz() {
    $this->readFeed(preg_replace('/^testMapping/', '', __FUNCTION__));
  }

  public function testMappingBoraBora() {
    $this->readFeed(preg_replace('/^testMapping/', '', __FUNCTION__));
  }

  public function testMappingBilletlugen() {
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
    $this->assertEquals(new \DateTime('2016-08-17T19:30:00', new \DateTimeZone('CEST')), $occurrence['startDate']);

    $event = $this->events[5];
    $this->assertEquals(['Show', 'Comedy', 'Stand-Up'], $event['tags']);
    $this->assertEquals(1, count($event['occurrences']));
  }

  private function readFeed(string $name) {
    $feedConfiguration = $this->readFixture($name .'.yml');
    $type = $feedConfiguration['type'];
    $data = $this->readFixture($name . '.' . $type);

    $feed = new Feed();
    $feed->setConfiguration($feedConfiguration);
    $this->converter = $this->container->get('value_converter');

    $this->events = [];
    $reader = $this->container->get('feed_reader.' . $type);
    $reader
      ->setController($this)
      ->setFeed($feed);
    $reader->read($data);
  }

  public function createEvent(array $data) {
    if (isset($data['image'])) {
      $data['original_image'] = $data['image'];
      // $data['image'] = $this->converter->downloadImage($data['image']);
    }

    $this->events[] = $data;
  }

  public function convertValue($value, $name) {
    return $this->converter->convert($value, $name);
  }
}
