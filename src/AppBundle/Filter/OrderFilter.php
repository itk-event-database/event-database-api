<?php

namespace AppBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter as BaseOrderFilter;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

/**
 *
 */
class OrderFilter extends BaseOrderFilter {
  protected function extractProperties(Request $request): array {
    $properties = parent::extractProperties($request);

    foreach ($this->properties as $property => $config) {
      if (!array_key_exists($property, $properties) && isset($config['default'])) {
        $properties[$property] = $config['default'];
      }
    }

    return $properties;
  }
}
