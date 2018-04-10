<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter as BaseOrderFilter;
use Symfony\Component\HttpFoundation\Request;

class OrderFilter extends BaseOrderFilter
{
    protected function extractProperties(Request $request): array
    {
        $properties = parent::extractProperties($request);

        foreach ($this->properties as $property => $config) {
            if (!array_key_exists($property, $properties) && isset($config['default'])) {
                $properties[$property] = $config['default'];
            }
        }

        return $properties;
    }
}
