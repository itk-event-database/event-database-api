<?php

namespace AdminBundle\Service\FeedReader;

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
  protected function jsonPath($data, $path, $failOnError = false) {
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

  private $parentSelector = 'parent::';

  protected function getData(array $item, array $mapping, array $rootPath = []) {
    $data = [];

    foreach ($mapping as $key => $spec) {
      if (!is_array($spec)) {
        $path = $spec;
        if (preg_match('/^(?<parents>(?:' . preg_quote($this->parentSelector, '/') . ')+)(?<path>.+)/', $path, $matches)) {
          $index = count($rootPath) - strlen($matches['parents']) / strlen($this->parentSelector);
          $item = $rootPath[$index];
          $path = $matches['path'];
        }
        $value = $this->jsonPath($item, $path);
        if ($value !== null) {
          $data[$key] = $this->convertValue($value, $key);
        }
      } else if (isset($spec['mapping'])) {
        $mapping = $spec['mapping'];
        $type = isset($spec['type']) ? $spec['type'] : 'list';
        $path = isset($spec['path']) ? $spec['path'] : '.';
        $items = ($path === '.') ? [ $item ] : $this->jsonPath($item, $path);
        if ($items) {
          array_push($rootPath, $item);
          if ($type === 'object' || $this->isAssoc($items)) {
            $data[$key] = $this->getData($items, $mapping, $rootPath);
          } else {
            $data[$key] = array_map(function($item) use ($mapping, $rootPath) {
              return $this->getData($item, $mapping, $rootPath);
            }, $items);
          }
          array_pop($rootPath);
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

  // @see http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
  private function isAssoc(array $arr) {
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
  }
}