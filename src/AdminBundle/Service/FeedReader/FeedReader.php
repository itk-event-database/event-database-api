<?php

namespace AdminBundle\Service\FeedReader;

use AdminBundle\Entity\Feed;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
  protected $controller;

  /**
   * @var ContainerInterface
   */
  protected $container;

  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

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
  protected function setDefaults(array &$data, array $defaults, array $item) {
    foreach ($defaults as $key => $spec) {
      $this->setDefaultValue($data, $key, $spec, $item);
    }
  }

  /**
   * @param array $data
   * @param string $key
   * @param $spec
   */
  private function setDefaultValue(array &$data, string $key, array $spec, array $item) {
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

    if (isset($spec['type'])) {
      $data[$key] = null;
      switch ($spec['type']) {
        case 'lookup':
          if (isset($spec['values'], $spec['key'])) {
            $lookupKey = isset($item[$spec['key']]) ? $item[$spec['key']] : (isset($data[$spec['key']]) ? $data[$spec['key']] : NULL);
            $data[$key] = isset($spec['values'][$lookupKey]) ? $spec['values'][$lookupKey] : null;
          }
          break;
        case 'service':
          if (isset($spec['service'])) {
            $serviceName = $spec['service'];
            if ($this->container->has($serviceName)) {
              $service = $this->container->get($serviceName);
              $methodName = isset($spec['method']) ? $spec['method'] : 'getValue';
              if (method_exists($service, $methodName)) {
                try {
                  $arguments = isset($spec['arguments']) ? $spec['arguments'] : [];
                  if (!is_array($arguments)) {
                    $arguments = [$arguments];
                  }
                  $arguments = array_map(function ($argument) use ($data, $item) {
                    if (preg_match('/^@(?<key>.+)$/', $argument, $matches)) {
                      $key = $matches['key'];
                      return $item[$key] ? $item[$key] : (isset($data[$key]) ? $data[$key] : NULL);
                    }
                    return $argument;
                  }, $arguments);
                  $data[$key] = call_user_func_array([$service, $methodName], $arguments);
                } catch (\Exception $ex) {}
              }
            }
          }
          break;
      }
    }
  }

}
