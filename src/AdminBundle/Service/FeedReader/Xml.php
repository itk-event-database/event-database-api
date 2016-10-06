<?php

namespace AdminBundle\Service\FeedReader;

class Xml extends FeedReader {
  public function read($data) {
    if (!$data instanceof \SimpleXMLElement) {
      throw new \Exception('Invalid data');
    }

    $events = $data;

    if (!empty($this->feed->getRoot())) {
      $events = $this->getItems($events, $this->feed->getRoot());
    }

    if ($events) {
      foreach ($events as $event) {
        $eventData = $this->getData($event, $this->feed->getMapping());
        $this->createEvent($eventData);
      }
    }
  }

  private function getItems(\SimpleXMLElement $el, $path, $failOnError = false) {
    $nodes = $el->xpath($path);
    if ($nodes === false) {
      if ($failOnError) {
        throw new \Exception('Invalid path: ' . $path);
      } else {
        return null;
      }
    }

    return $nodes;
  }

  /**
   * Get a single value from xpath
   */
  private function getValue(\SimpleXMLElement $el, $path, $failOnError = false) {
    $values = $this->getItems($el, $path, $failOnError);
    return (count($values) > 0) ? (string)$values[0] : null;
  }

  private function getData(\SimpleXMLElement $item, array $mapping) {
    $data = [];

    foreach ($mapping as $key => $spec) {
      if (!is_array($spec)) {
        $path = $spec;
        $value = $this->getValue($item, $path);
        if ($value !== null) {
          $data[$key] = $this->convertValue($value, $key);
        }
      } else if (isset($spec['mapping'])) {
        $mapping = $spec['mapping'];
        $path = isset($spec['path']) ? $spec['path'] : '.';
        $items = ($path === '.') ? [ $item ] : $this->getItems($item, $path);
        if ($items) {
          $data[$key] = array_map(function($item) use ($mapping) {
            return $this->getData($item, $mapping);
          }, $items);
        }
      } else if (isset($spec['path'])) {
        $path = $spec['path'];
        $value = $this->getValue($item, $path);
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