<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service\FeedReader;

use AdminBundle\Entity\Feed;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class FeedReader
{
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param \AdminBundle\Service\FeedReader\Controller $controller
     *
     * @return $this
     */
    public function setController(Controller $controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @param \AdminBundle\Entity\Feed $feed
     *
     * @return $this
     */
    public function setFeed(Feed $feed)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * @param $data
     *
     * @return
     */
    abstract public function read($data);

    /**
     * @param $value
     * @param $key
     *
     * @return
     */
    protected function convertValue($value, $key)
    {
        return $this->controller->convertValue($value, $key);
    }

    /**
     * @param array $data
     *
     * @return
     */
    protected function createEvent(array $data)
    {
        return $this->controller->createEvent($data);
    }

    /**
     * @param array $data
     * @param array $defaults
     */
    protected function setDefaults(array &$data, array $defaults, array $item)
    {
        foreach ($defaults as $key => $spec) {
            $this->setDefaultValue($data, $key, $spec, $item);
        }
    }

    /**
     * @param array  $data
     * @param string $key
     * @param $spec
     */
    private function setDefaultValue(array &$data, string $key, $spec, array $item)
    {
        if ('endDate' === $key && isset($data['startDate']) && (empty($data[$key]) || $data[$key] <= $data['startDate'])) {
            $startDate = $data['startDate'];
            if ($startDate instanceof \DateTime) {
                try {
                    $endDate = clone $startDate;
                    $timeZone = $this->feed->getTimeZone();
                    if (null !== $timeZone) {
                        $endDate->setTimeZone($timeZone);
                    }
                    $endDate->modify($spec);
                    $endDate->setTimeZone(new \DateTimeZone('UTC'));
                    $data[$key] = $endDate;

                    return;
                } catch (\Exception $e) {
                }
            }
        }

        if (empty($data[$key])) {
            $data[$key] = isset($spec['value']) ? $spec['value'] : $spec;
        } elseif (isset($spec['append']) && $spec['append']) {
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

        if (isset($spec['type'])) {
            $value = null;
            switch ($spec['type']) {
                case 'map':
                    $value = $this->getMapValue($spec, $item);

                    break;
                case 'service':
                    $value = $this->getServiceValue($spec, $item);

                    break;
            }

            if (null === $value && isset($spec['default'])) {
                $value = $spec['default'];
            }

            $data[$key] = $value;
        }
    }

    private function getMapValue(array $spec, array $item)
    {
        if (isset($spec['map'], $spec['key'])) {
            $key = $this->expandValue($spec['key'], $item, []);

            return isset($spec['map'][$key]) ? $spec['map'][$key] : null;
        }

        return null;
    }

    private function getServiceValue(array $spec, array $item)
    {
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
                        $arguments = array_map(function ($argument) use ($item) {
                            return $this->expandValue($argument, $item, []);
                        }, $arguments);

                        return call_user_func_array([$service, $methodName], $arguments);
                    } catch (\Exception $ex) {
                        throw $ex;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get value from data array. Expand '@key' to value of $item[key] or $data[key].
     */
    private function expandValue(string $value, array $item, array $data)
    {
        if (preg_match('/^@(?<key>.+)$/', $value, $matches)) {
            $key = $matches['key'];

            return $item[$key] ? $item[$key] : (isset($data[$key]) ? $data[$key] : null);
        }

        return $value;
    }
}
