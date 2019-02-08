<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service\FeedReader;

class Xml extends FeedReader
{
    /**
     * @param $data
     *
     * @throws \Exception
     */
    public function read($data)
    {
        if (!$data instanceof \SimpleXMLElement) {
            throw new \Exception('Invalid data');
        }

        $events = $data;

        if (!empty($this->feed->getRoot())) {
            $events = $this->getItems($events, $this->feed->getRoot());
        }

        if ($events) {
            foreach ($events as $event) {
                $eventData = $this->getData($event, $this->feed->getConfiguration());
                $this->createEvent($eventData);
            }
        }
    }

    /**
     * @param \SimpleXMLElement $el
     * @param $path
     * @param bool $failOnError
     *
     * @throws \Exception
     *
     * @return null|\SimpleXMLElement[]
     */
    private function getItems(\SimpleXMLElement $el, $path, $failOnError = false)
    {
        if (!$path) {
            return null;
        }
        $nodes = $el->xpath($path);
        if (false === $nodes) {
            if ($failOnError) {
                throw new \Exception('Invalid path: '.$path);
            }

            return null;
        }

        return $nodes;
    }

    /**
     * Get a single value from xpath.
     *
     * @param \SimpleXMLElement $el
     * @param $path
     * @param bool $failOnError
     *
     * @return null|string
     *
     * @throws \Exception
     */
    private function getValue(\SimpleXMLElement $el, $path, $failOnError = false)
    {
        if (!$path) {
            return null;
        }

        $values = $this->getItems($el, $path, $failOnError);

        return (is_array($values) && count($values) > 0) ? trim((string) $values[0]) : null;
    }

    /**
     * @param \SimpleXMLElement $item
     * @param array             $configuration
     *
     * @return array
     */
    private function getData(\SimpleXMLElement $item, array $configuration)
    {
        $data = [];

        $mapping = $configuration['mapping'];

        foreach ($mapping as $key => $spec) {
            if (!is_array($spec)) {
                $path = $spec;
                $value = $this->getValue($item, $path);
                if (null !== $value) {
                    $data[$key] = $this->convertValue($value, $key);
                }
            } elseif (isset($spec['mapping'])) {
                $type = isset($spec['type']) ? $spec['type'] : 'list';
                $path = isset($spec['path']) ? $spec['path'] : null;
                if ('object' === $type) {
                    $item = $path ? $this->getItems($item, $path) : $item;
                    if (is_array($item)) {
                        $item = array_shift($item);
                    }
                    $data[$key] = $this->getData($item, $spec);
                } else {
                    $items = $path ? $this->getItems($item, $path) : [$item];
                    if ($items) {
                        if ('object' === $type) {
                            $data[$key] = $this->getData($items, $spec);
                        } else {
                            $data[$key] = array_map(function ($item) use ($spec) {
                                return $this->getData($item, $spec);
                            }, $items);
                        }
                    }
                }
            } elseif (isset($spec['path'])) {
                $path = $spec['path'];
                $value = $this->getValue($item, $path);
                if (null !== $value) {
                    if (isset($spec['split'])) {
                        $data[$key] = preg_split('/\s*'.preg_quote($spec['split'], '/').'\s*/', $value, null, PREG_SPLIT_NO_EMPTY);
                    } else {
                        $data[$key] = $this->convertValue($value, $key);
                    }
                }
            }
        }

        if (isset($configuration['defaults'])) {
            // @FIXME: We must be able to pass $item as an array to setDefaults.
            $this->setDefaults($data, $configuration['defaults'], []);
        }

        return $data;
    }
}
