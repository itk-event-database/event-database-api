<?php

namespace AdminBundle\Service\FeedReader;

use AdminBundle\Entity\Feed;

/**
 *
 */
abstract class FeedReader {
  /**
   * @var Feed
   */
  protected $feed;

  /**
   * @var Controller
   */
  private $controller;

  /**
   * @param \AdminBundle\Service\FeedReader\Controller $controller
   * @return $this
   */
  public function setController(Controller $controller) {
    $this->controller = $controller;

    return $this;
  }

  /**
   * @param \AdminBundle\Entity\Feed $feed
   * @return $this
   */
  public function setFeed(Feed $feed) {
    $this->feed = $feed;

    return $this;
  }

  /**
   * @param $data
   * @return
   */
  public abstract function read($data);

  /**
   * @param $value
   * @param $key
   * @return
   */
  protected function convertValue($value, $key) {
    return $this->controller->convertValue($value, $key);
  }

  /**
   * @param array $data
   * @return
   */
  protected function createEvent(array $data) {
    return $this->controller->createEvent($data);
  }

  /**
   * @param array $data
   * @param array $defaults
   */
  protected function setDefaults(array &$data, array $defaults) {
    foreach ($defaults as $key => $spec) {
      $this->setDefaultValue($data, $key, $spec);
    }
  }

  /**
   * @param array $data
   * @param string $key
   * @param $spec
   */
  private function setDefaultValue(array &$data, string $key, $spec) {
    if (empty($data[$key])) {
      $data[$key] = isset($spec['value']) ? $spec['value'] : $spec;
    }
    elseif (isset($spec['append']) && $spec['append'] == 'true') {
      if (is_array($data[$key])) {
        if (is_array($spec['value'])) {
          foreach ($spec['value'] as $item) {
            $data[$key][] = $item;
          }
        }
        else {
          $data[$key][] = $spec['value'];
        }
      }
    }
  }

}
