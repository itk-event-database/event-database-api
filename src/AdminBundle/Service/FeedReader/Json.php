<?php

namespace AdminBundle\Service\FeedReader;

use AdminBundle\Service\FeedReader;

class Json extends FeedReader {
  public function read($data) {
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

  // http://goessner.net/articles/JsonPath/
  private function jsonPath($data, $path, $failOnError = false) {
    $steps = preg_split('@\s*\.\s*@', $path);
    foreach ($steps as $step) {
      if (!isset($data[$step])) {
        if ($failOnError) {
          throw new \Exception('Invalid path: ' . $path);
        } else {
          return null;
        }
      }
      $data = $data[$step];
    }
    return $data;
  }

  private function getData(array $item, array $mapping) {
    $data = [];

    foreach ($mapping as $key => $spec) {
      if (!is_array($spec)) {
        $path = $spec;
        $value = $this->jsonPath($item, $path);
        if ($value !== null) {
          $data[$key] = $this->convertValue($value, $key);
        }
      } else if (isset($spec['mapping'])) {
        $mapping = $spec['mapping'];
        $path = isset($spec['path']) ? $spec['path'] : '.';
        $items = ($path === '.') ? [ $item ] : $this->jsonPath($item, $path);
        if ($items) {
          $data[$key] = array_map(function($item) use ($mapping) {
            return $this->getData($item, $mapping);
          }, $items);
        }
      } else if (isset($spec['path'])) {
        $path = $spec['path'];
        $value = $this->jsonPath($item, $path);
        if ($value !== null) {
          if (isset($spec['split'])) {
            $data[$key] = preg_split('/\s*' . preg_quote($spec['split'], '/') . '\s*/', $value, null, PREG_SPLIT_NO_EMPTY);
          } else {
            $data[$key] = $this->convertValue($value, $key);
          }
        }
      }
    }

    return $data;
  }

}