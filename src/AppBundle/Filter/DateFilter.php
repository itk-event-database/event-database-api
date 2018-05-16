<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter as BaseDateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

class DateFilter extends BaseDateFilter
{
    /**
     * Apply default filter values.
     *
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null, array $context = [])
    {
        foreach ($this->properties as $property => $config) {
            if (!array_key_exists($property, $context['filters']) && isset($config['default'])) {
                $context['filters'][$property] = $config['default'];
            }
        }

        return parent::apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);
    }

    /**
     * We override this function to allow more parameters on properties (default value).
     *
     * {@inheritdoc}
     */
    protected function filterProperty(string $property, $values, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        // Expect $values to be an array having the period as keys and the date value as values
        if (!\is_array($values) ||
            !$this->isPropertyEnabled($property, $resourceClass) ||
            !$this->isPropertyMapped($property, $resourceClass) ||
            !$this->isDateField($property, $resourceClass)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $field = $property;

        if ($this->isPropertyNested($property, $resourceClass)) {
            list($alias, $field) = $this->addJoinsForNestedProperty($property, $alias, $queryBuilder, $queryNameGenerator, $resourceClass);
        }

        $nullManagement = $this->properties[$property] ?? null;
        $type = $this->getDoctrineFieldType($property, $resourceClass);

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
                $nullManagement,
                $type
            );
        }

        if (isset($values[self::PARAMETER_STRICTLY_BEFORE])) {
            $this->addWhere(
                $queryBuilder,
                $queryNameGenerator,
                $alias,
                $field,
                self::PARAMETER_STRICTLY_BEFORE,
                $values[self::PARAMETER_STRICTLY_BEFORE],
                $nullManagement,
                $type
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
                $nullManagement,
                $type
            );
        }

        if (isset($values[self::PARAMETER_STRICTLY_AFTER])) {
            $this->addWhere(
                $queryBuilder,
                $queryNameGenerator,
                $alias,
                $field,
                self::PARAMETER_STRICTLY_AFTER,
                $values[self::PARAMETER_STRICTLY_AFTER],
                $nullManagement,
                $type
            );
        }
    }
}
