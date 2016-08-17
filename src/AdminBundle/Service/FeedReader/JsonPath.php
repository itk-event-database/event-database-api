<?php

namespace AdminBundle\Service\FeedReader;

use JsonPath\JsonObject;

class JsonPath extends Json {
  // http://goessner.net/articles/JsonPath/
  protected function jsonPath($data, $path, $failOnError = false) {
    $json = new JsonObject($data, true);
    // echo var_dump([array_keys($data), $path, array_keys($json->get('$.' . $path))], true); die(__FILE__.':'.__LINE__.':'.__METHOD__);
    return $json->get('$.' . $path);
  }

  public function hest___read($data) {
    if (!is_array($data)) {
      throw new \Exception('Invalid data');
    }

    $events = $data;

    if (!empty($this->feed->getRoot())) {
      $events = $this->jsonPath($events, $this->feed->getRoot());
    }
    if ($events) {
      foreach ($events as $event) {
        $eventData = $this->getData($event, $this->feed->getMapping());
        $this->createEvent($eventData);
      }
    }
  }

}
