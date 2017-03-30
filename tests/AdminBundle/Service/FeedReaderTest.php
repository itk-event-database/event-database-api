<?php

namespace AdminBundle\Service;

use Tests\AppBundle\Test\ContainerTestCase;

use AdminBundle\Entity\Feed;
use AdminBundle\Service\FeedReader\Controller;
use AdminBundle\Service\FeedReader\ValueConverter;

class FeedReaderTest extends ContainerTestCase implements Controller {
  /**
   * @var ValueConverter
   */
  private $converter;

  /**
   * @var FileHandler
   */
  private $fileHandler;

  private $events = [];

  public function testReadJsonFeedWithDefaults() {
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

  public function testReadJsonFeedWithDefaultsIndex() {
    $feedConfiguration = $this->readFixture('feed-with-defaults-index.config.yml');
    $json = $this->readFixture('feed-with-defaults-index.data.yml');

    $feed = $this->createFeed($feedConfiguration);

    $reader = $this->container->get('feed_reader.json');
    $reader
      ->setController($this)
      ->setFeed($feed);
    $reader->read($json);

    $this->assertEquals(1, count($this->events));
    $occurrences = $this->events[0]['occurrences'];
    $this->assertEquals(3, count($occurrences));
    $occurrence = $occurrences[0];
    $this->assertEquals([
      'name' => 'Place 1',
      'postal_code' => 8000,
      'address_locality' => 'Aarhus C',
    ], $occurrence['place']);

    $occurrence = $occurrences[1];
    $this->assertEquals([
      'name' => 'Place 2',
      'postal_code' => 7400,
      'address_locality' => 'Herning',
    ], $occurrence['place']);

    $occurrence = $occurrences[2];
    $this->assertEquals([
      'name' => 'Place 3',
      'postal_code' => 1234,
      'address_locality' => 'Andeby',
    ], $occurrence['place']);
  }

  public function testReadJsonFeedWithDefaultsMap() {
    $feedConfiguration = $this->readFixture('feed-with-defaults-map.config.yml');
    $json = $this->readFixture('feed-with-defaults-map.data.yml');

    $feed = $this->createFeed($feedConfiguration);

    $reader = $this->container->get('feed_reader.json');
    $reader
      ->setController($this)
      ->setFeed($feed);
    $reader->read($json);

    $this->assertEquals(1, count($this->events));
    $occurrences = $this->events[0]['occurrences'];
    $this->assertEquals(3, count($occurrences));
    $occurrence = $occurrences[0];
    $this->assertEquals([
      'name' => 'Place 1',
      'postal_code' => 8000,
      'address_locality' => 'Aarhus C',
    ], $occurrence['place']);

    $occurrence = $occurrences[1];
    $this->assertEquals([
      'name' => 'Place 2',
      'postal_code' => 8310,
      'address_locality' => 'Tranbjerg J',
    ], $occurrence['place']);

    $occurrence = $occurrences[2];
    $this->assertEquals([
      'name' => 'Place 3',
      'postal_code' => 1234,
      'address_locality' => 'Andeby',
    ], $occurrence['place']);
  }

  public function testReadJsonFeedWithDefaultsMapNoDefaultValue() {
    $feedConfiguration = $this->readFixture('feed-with-defaults-map-no-default-value.config.yml');
    $json = $this->readFixture('feed-with-defaults-map.data.yml');

    $feed = $this->createFeed($feedConfiguration);

    $reader = $this->container->get('feed_reader.json');
    $reader
      ->setController($this)
      ->setFeed($feed);
    $reader->read($json);

    $this->assertEquals(1, count($this->events));
    $occurrences = $this->events[0]['occurrences'];
    $this->assertEquals(3, count($occurrences));
    $occurrence = $occurrences[0];
    $this->assertEquals([
      'name' => 'Place 1',
      'postal_code' => 8000,
      'address_locality' => 'Aarhus C',
    ], $occurrence['place']);

    $occurrence = $occurrences[1];
    $this->assertEquals([
      'name' => 'Place 2',
      'postal_code' => 8310,
      'address_locality' => 'Tranbjerg J',
    ], $occurrence['place']);

    $occurrence = $occurrences[2];
    $this->assertEquals([
      'name' => 'Place 3',
      'postal_code' => 1234,
      'address_locality' => NULL,
    ], $occurrence['place']);
  }

  public function testReadJsonFeedWithDefaultsService() {
    $feedConfiguration = $this->readFixture('feed-with-defaults-service.config.yml');
    $json = $this->readFixture('feed-with-defaults-service.data.yml');

    $feed = $this->createFeed($feedConfiguration);

    $reader = $this->container->get('feed_reader.json');
    $reader
      ->setController($this)
      ->setFeed($feed);
    $reader->read($json);

    $this->assertEquals(1, count($this->events));
    $occurrences = $this->events[0]['occurrences'];
    $this->assertEquals(3, count($occurrences));
    $occurrence = $occurrences[0];
    $this->assertEquals([
      'name' => 'Place 1',
      'postal_code' => 8000,
      'address_locality' => 'Aarhus C',
    ], $occurrence['place']);

    $occurrence = $occurrences[1];
    $this->assertEquals([
      'name' => 'Place 2',
      'postal_code' => 8310,
      'address_locality' => 'Tranbjerg J',
    ], $occurrence['place']);

    $occurrence = $occurrences[2];
    $this->assertEquals([
      'name' => 'Place 3',
      'postal_code' => 1234,
      'address_locality' => 'Andeby',
    ], $occurrence['place']);
  }

  public function testReadJsonFeedWithDefaultsServiceNoDefaultValue() {
    $feedConfiguration = $this->readFixture('feed-with-defaults-service-no-default-value.config.yml');
    $json = $this->readFixture('feed-with-defaults-service.data.yml');

    $feed = $this->createFeed($feedConfiguration);

    $reader = $this->container->get('feed_reader.json');
    $reader
      ->setController($this)
      ->setFeed($feed);
    $reader->read($json);

    $this->assertEquals(1, count($this->events));
    $occurrences = $this->events[0]['occurrences'];
    $this->assertEquals(3, count($occurrences));
    $occurrence = $occurrences[0];
    $this->assertEquals([
      'name' => 'Place 1',
      'postal_code' => 8000,
      'address_locality' => 'Aarhus C',
    ], $occurrence['place']);

    $occurrence = $occurrences[1];
    $this->assertEquals([
      'name' => 'Place 2',
      'postal_code' => 8310,
      'address_locality' => 'Tranbjerg J',
    ], $occurrence['place']);

    $occurrence = $occurrences[2];
    $this->assertEquals([
      'name' => 'Place 3',
      'postal_code' => 1234,
      'address_locality' => NULL,
    ], $occurrence['place']);
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
    $this->converter->setFeed($feed);
    $this->fileHandler = $this->container->get('file_handler');

    return $feed;
  }

  public function createEvent(array $data) {
    if (isset($data['image'])) {
      $data['original_image'] = $data['image'];
      $data['image'] = $this->fileHandler->download($data['image']);
    }

    $this->events[] = $data;
  }

  public function convertValue($value, $name) {
    return $this->converter->convert($value, $name);
  }

}
