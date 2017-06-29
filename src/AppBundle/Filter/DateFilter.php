<?php

namespace AppBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter as BaseDateFilter;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

/**
 *
 */
class DateFilter extends BaseDateFilter
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

  /**
   * We override this function to allow more parameters on properties (default value).
   *
   * {@inheritdoc}
   */
    protected function filterProperty(string $property, $values, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        // Expect $values to be an array having the period as keys and the date value as values
        if (!is_array($values) ||
        !$this->isPropertyEnabled($property) ||
        !$this->isPropertyMapped($property, $resourceClass) ||
        !$this->isDateField($property, $resourceClass)
        ) {
            return;
        }

        $alias = 'o';
        $field = $property;

        if ($this->isPropertyNested($property)) {
            list($alias, $field) = $this->addJoinsForNestedProperty($property, $alias, $queryBuilder, $queryNameGenerator);
        }

        // This is the only change compared to parent::filterProperty.
        $nullManagement = null;
        if (is_string($this->properties[$property])) {
            $nullManagement = $this->properties[$property];
        } elseif (isset($this->properties[$property]['null_management'])) {
            $nullManagement = $this->properties[$property]['null_management'];
        }
        // End change

        if (self::EXCLUDE_NULL === $nullManagement) {
            $queryBuilder->andWhere($queryBuilder->expr()->isNotNull(sprintf('%s.%s', $alias, $field)));
        }

        if (isset($values[self::PARAMETER_BEFORE])) {
            $this->addWhere(
                $queryBuilder,
                $queryNameGenerator,
                $alias,
                $field,
                self::PARAMETER_BEFORE,
                $values[self::PARAMETER_BEFORE],
                $nullManagement
            );
        }

        if (isset($values[self::PARAMETER_AFTER])) {
            $this->addWhere(
                $queryBuilder,
                $queryNameGenerator,
                $alias,
                $field,
                self::PARAMETER_AFTER,
                $values[self::PARAMETER_AFTER],
                $nullManagement
            );
        }
    }
}
