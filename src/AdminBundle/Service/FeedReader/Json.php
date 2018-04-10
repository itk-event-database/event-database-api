<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service\FeedReader;

use JsonPath\JsonObject;

class Json extends FeedReader
{
    private $parentSelector = 'parent::';

    /**
     * @param $data
     *
     * @throws \Exception
     */
    public function read($data)
    {
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

    /**
     * Http://goessner.net/articles/JsonPath.
     *
     * @param $data
     * @param $path
     * @param bool $failOnError
     *
     * @return mixed
     */
    protected function getValue($data, $path, $failOnError = false)
    {
        if (!$path) {
            return null;
        }
        $json = new JsonObject($data, true);
        $prefix = 0 === strpos($path, '[') ? '$' : '$.';

        return $json->get($prefix.$path);
    }

    /**
     * @param array $item
     * @param array $configuration
     * @param array $rootPath
     *
     * @return array
     */
    protected function getData(array $item = null, array $configuration, array $rootPath = [])
    {
        if (!$item) {
            return null;
        }
        $data = [];

        $mapping = $configuration['mapping'];

        foreach ($mapping as $key => $spec) {
            if (!is_array($spec)) {
                $path = $spec;
                if (preg_match('/^(?<parents>(?:'.preg_quote($this->parentSelector, '/').')+)(?<path>.+)/', $path, $matches)) {
                    $index = count($rootPath) - strlen($matches['parents']) / strlen($this->parentSelector);
                    $item = $rootPath[$index];
                    $path = $matches['path'];
                }
                $value = $this->getValue($item, $path);
                if (null !== $value) {
                    $data[$key] = $this->convertValue($value, $key);
                }
            } elseif (isset($spec['mapping'])) {
                $type = isset($spec['type']) ? $spec['type'] : 'list';
                $path = isset($spec['path']) ? $spec['path'] : null;
                array_push($rootPath, $item);
                if ('object' === $type) {
                    $item = $path ? $this->getValue($item, $path) : $item;
                    $data[$key] = $this->getData($item, $spec, $rootPath);
                } else {
                    $items = $path ? $this->getValue($item, $path) : [$item];
                    if ($items) {
                        if ('object' === $type || $this->isAssoc($items)) {
                            $data[$key] = $this->getData($items, $spec, $rootPath);
                        } else {
                            $data[$key] = array_map(function ($item) use ($spec, $rootPath) {
                                return $this->getData($item, $spec, $rootPath);
                            }, $items);
                        }
                    }
                }
                array_pop($rootPath);
            } elseif (isset($spec['path'])) {
                $path = $spec['path'];
                $value = $this->getValue($item, $path);
                if (null !== $value) {
                    if (isset($spec['split'])) {
                        $limit = isset($spec['limit']) ? $spec['limit'] : null;
                        $values = preg_split('/\s*'.preg_quote($spec['split'], '/').'\s*/', $value, $limit, PREG_SPLIT_NO_EMPTY);
                        // @TODO: Generalize this and move into parent class.
                        if (isset($spec['trim'])) {
                            $values = array_map('trim', $values);
                        }
                        if (isset($spec['index'])) {
                            $values = isset($values[$spec['index']]) ? $values[$spec['index']] : null;
                        }
                        $data[$key] = $values;
                    } else {
                        $data[$key] = $this->convertValue($value, $key);
                    }
                }
            }
        }

        if (isset($configuration['defaults'])) {
            $this->setDefaults($data, $configuration['defaults'], $item);
        }

        return $data;
    }

    /**
     * @see http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
     *
     * @param array $arr
     *
     * @return bool
     */
    private function isAssoc(array $arr)
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
