<?php

namespace AdminBundle\Service;

use Tests\AppBundle\Test\ContainerTestCase;

use AdminBundle\Entity\Feed;
use AdminBundle\Service\FeedReader\Controller;
use AdminBundle\Service\FeedReader\ValueConverter;

class FeedReaderTest extends ContainerTestCase implements Controller {
  private $converter;
  private $events = [];

  public function testReadJSONFeedWithDefaults() {
    $feedConfiguration = $this->readFixture('feed-with-defaults.yml');
    $json = $this->readFixture('feed-with-defaults.json');

    $feed = $this->createFeed($feedConfiguration);

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
    $xml = $this->readFixture('feed-with-defaults.xml');

    $feed = $this->createFeed($feedConfiguration);

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

  public function testReadFeedWithImages() {
    $feedConfiguration = $this->readFixture('feed-with-images.yml');
    $json = $this->readFixture('feed-with-images.json');

    $feed = $this->createFeed($feedConfiguration);

    $reader = $this->container->get('feed_reader.json');
    $reader
      ->setController($this)
      ->setFeed($feed);
    $reader->read($json);

    $this->assertEquals(24, count($this->events));
    $event = $this->events[0];
    $this->assertEquals(6, count($event['occurrences']));
    $this->assertEquals('Musikhuset Aarhus', $event['occurrences'][0]['venue']);
    $this->assertEquals('http://musikhusetaarhus.dk/media/2738/kultur-3.jpg', $event['original_image']);
  }

  private function createFeed(array $configuration) {
    $feed = new Feed();
    $feed->setConfiguration($configuration);
    $this->converter = $this->container->get('value_converter');

    return $feed;
  }

  public function createEvent(array $data) {
    if (isset($data['image'])) {
      $data['original_image'] = $data['image'];
      $data['image'] = $this->converter->downloadImage($data['image']);
    }

    $this->events[] = $data;
  }

  public function convertValue($value, $name) {
    return $this->converter->convert($value, $name);
  }
}
