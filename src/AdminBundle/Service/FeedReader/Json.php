<?php

namespace AdminBundle\Service\FeedReader;

use JsonPath\JsonObject;

class Json extends FeedReader {
  public function read($data) {
    if (!is_array($data)) {
      throw new \Exception('Invalid data');
    }

    $events = $data;

    if (!empty($this->feed->getRoot())) {
      $events = $this->getValue($events, $this->feed->getRoot());
    }

    if ($events) {
      foreach ($events as $event) {
        if (!is_array($event)) {
          continue;
        }
        $eventData = $this->getData($event, $this->feed->getConfiguration());
        $this->createEvent($eventData);
      }
    }
  }

  // http://goessner.net/articles/JsonPath/
  protected function getValue($data, $path, $failOnError = false) {
    $json = new JsonObject($data, true);
    $prefix = strpos($path, '[') === 0 ? '$' : '$.';
    return $json->get($prefix . $path);
  }

  private $parentSelector = 'parent::';

  protected function getData(array $item, array $configuration, array $rootPath = []) {
    $data = [];

    $mapping = $configuration['mapping'];

    foreach ($mapping as $key => $spec) {
      if (!is_array($spec)) {
        $path = $spec;
        if (preg_match('/^(?<parents>(?:' . preg_quote($this->parentSelector, '/') . ')+)(?<path>.+)/', $path, $matches)) {
          $index = count($rootPath) - strlen($matches['parents']) / strlen($this->parentSelector);
          $item = $rootPath[$index];
          $path = $matches['path'];
        }
        $value = $this->getValue($item, $path);
        if ($value !== null) {
          $data[$key] = $this->convertValue($value, $key);
        }
      } else if (isset($spec['mapping'])) {
        $type = isset($spec['type']) ? $spec['type'] : 'list';
        $path = isset($spec['path']) ? $spec['path'] : null;
        array_push($rootPath, $item);
        if ($type === 'object') {
          $item = $path ? $this->getValue($item, $path) : $item;
          $data[$key] = $this->getData($item, $spec, $rootPath);
        } else {
          $items = $path ? $this->getValue($item, $path) : [$item];
          if ($items) {
            if ($type === 'object' || $this->isAssoc($items)) {
              $data[$key] = $this->getData($items, $spec, $rootPath);
            } else {
              $data[$key] = array_map(function($item) use ($spec, $rootPath) {
                return $this->getData($item, $spec, $rootPath);
              }, $items);
            }
          }
        }
        array_pop($rootPath);
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

    if (isset($configuration['defaults'])) {
      $this->setDefaults($data, $configuration['defaults']);
    }

    return $data;
  }

  // @see http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
  private function isAssoc(array $arr) {
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
  }
}