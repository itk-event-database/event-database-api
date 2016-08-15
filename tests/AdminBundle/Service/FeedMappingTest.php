<?php

namespace AdminBundle\Service;

use Tests\AppBundle\Test\ContainerTestCase;

use AdminBundle\Entity\Feed;
use AdminBundle\Service\FeedReader\Controller;

class FeedMappingTest extends ContainerTestCase implements Controller {
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
    $this->assertEquals(3, count($event['occurrences']));
    $this->assertEquals(['Udstilling', 'Gratis'], $event['tags']);

    $event = $this->events[1];
    $this->assertEquals(1, count($event['occurrences']));
  }

  public function testMappingLivejazz() {
    $this->readFeed(preg_replace('/^testMapping/', '', __FUNCTION__));
  }

  public function testMappingBoraBora() {
    $this->readFeed(preg_replace('/^testMapping/', '', __FUNCTION__));
  }

  private function readFeed(string $name) {
    $feedConfiguration = $this->readFixture($name .'.yml');
    $type = $feedConfiguration['type'];
    $data = $this->readFixture($name . '.' . $type);

    $feed = new Feed();
    $feed->setConfiguration($feedConfiguration);

    $this->events = [];
    $reader = $this->container->get('feed_reader.' . $type);
    $reader
      ->setController($this)
      ->setFeed($feed);
    $reader->read($data);
  }

  public function convertValue($value, $name) {
    return $value;
  }

  public function createEvent(array $data) {
    $this->events[] = $data;
  }
}
