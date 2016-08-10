<?php

namespace AdminBundle\Service;

use AdminBundle\Service\FeedReader\Controller;
use AdminBundle\Entity\Feed;

abstract class FeedReader {
  protected $feed;
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
    $defaults = $this->feed->getDefaults();
    if ($defaults) {
      $this->setDefaults($data, $defaults);
    }
    return $this->controller->createEvent($data);
  }

  private function setDefaults(array &$data, array $defaults) {
    foreach ($defaults as $key => $spec) {
      switch ($key) {
        case 'occurrences':
          if (isset($data['occurrences'])) {
            foreach ($data['occurrences'] as &$occurrence) {
              $this->setDefaults($occurrence, $spec);
            }
          }
          break;
        default:
          $this->setDefaultValue($data, $key, $spec);
          break;
      }
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