<?php

namespace AdminBundle\Service\FeedReader;

use AdminBundle\Entity\Feed;

abstract class FeedReader {
  /**
   * @var Feed
   */
  protected $feed;

  /**
   * @var Controller
   */
  private $controller;

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

  protected function setDefaults(array &$data, array $defaults) {
    foreach ($defaults as $key => $spec) {
      $this->setDefaultValue($data, $key, $spec);
    }
  }

  private function setDefaultValue(array &$data, string $key, $spec) {
    if (empty($data[$key])) {
      $data[$key] = isset($spec['value']) ? $spec['value'] : $spec;
    } elseif (isset($spec['append']) && $spec['append'] == 'true') {
      if (is_array($data[$key])) {
        if (is_array($spec['value'])) {
          foreach ($spec['value'] as $item) {
            $data[$key][] = $item;
          }
        } else {
          $data[$key][] = $spec['value'];
        }
      }
    }
  }
}