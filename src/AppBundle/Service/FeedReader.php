<?php

namespace AppBundle\Service;

use AppBundle\Service\FeedReader\Controller;
use AppBundle\Entity\Feed;

abstract class FeedReader {
  protected $feed;
  protected $controller;

  public function setController(Controller $controller) {
    $this->controller = $controller;

    return $this;
  }

  public function setFeed(Feed $feed) {
    $this->feed = $feed;

    return $this;
  }

  public abstract function read($data);

  protected function convertValue($value, $key) {
    return $this->controller->convertValue($value, $key);
  }

  protected function createEvent(array $data) {
    return $this->controller->createEvent($data);
  }
}