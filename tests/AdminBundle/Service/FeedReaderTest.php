<?php

namespace AdminBundle\Service;

use Tests\AppBundle\Test\ContainerTestCase;

use AdminBundle\Entity\Feed;
use AdminBundle\Service\FeedReader\Controller;

class FeedReaderTest extends ContainerTestCase implements Controller {
  private $events = [];

  public function testReadJSONFeedWithDefaults() {
    $feedConfiguration = $this->readFixture('feed-with-defaults.yml');
    $json = $this->readFixture('feed-with-defaults.json');

    $feed = new Feed();
    $feed
      ->setConfiguration($feedConfiguration);

    $reader = $this->container->get('feed_reader.json');
    $reader
      ->setController($this)
      ->setFeed($feed);
    $reader->read($json);

    $this->assertEquals(10, count($this->events));
    $event = $this->events[0];
    $this->assertEquals(['app_festival', 'Music', 'NO', 'UrbanMusicNetwork', 'LineUp', 'SPOT Festival'], $event['tags']);
    $this->assertEquals(1, count($event['occurrences']));
    $this->assertEquals('Musikhuset Aarhus', $event['occurrences'][0]['venue']);
  }

  public function testReadXmlFeedWithDefaults() {
    $feedConfiguration = $this->readFixture('feed-with-defaults.yml');
    $xml = new \SimpleXmlElement($this->readFixture('feed-with-defaults.xml'));

    $feed = new Feed();
    $feed
      ->setConfiguration($feedConfiguration);

    $reader = $this->container->get('feed_reader.xml');
    $reader
      ->setController($this)
      ->setFeed($feed);
    $reader->read($xml);

    $this->assertEquals(2, count($this->events));
    $event = $this->events[0];
    $this->assertEquals(['app_festival', 'Music', 'NO', 'UrbanMusicNetwork', 'LineUp', 'SPOT Festival'], $event['tags']);
    $this->assertEquals(1, count($event['occurrences']));
    $this->assertEquals('Musikhuset Aarhus', $event['occurrences'][0]['venue']);
  }

  public function convertValue($value, $name) {
      return $value;
  }

  public function createEvent(array $data) {
    $this->events[] = $data;
  }
}
