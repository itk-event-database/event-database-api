<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use AppBundle\Entity\DailyOccurrence;
use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AccessFilter
 */
class AccessFilter extends AbstractFilter
{
    private const PROPERTY = 'access';
    private const ALL = 'all';
    private const PUBLIC = 'public';
    private const PRIVATE = 'private';

    private const ALLOWED_VALUES = [self::ALL, self::PRIVATE, self::PUBLIC];

    /** {@inheritDoc} */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (Event::class !== $resourceClass && Occurrence::class !== $resourceClass && DailyOccurrence::class !== $resourceClass) {
            return false;
        }

        return parent::apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName);
    }

    /** {@inheritDoc} */
    public function getDescription(string $resourceClass): array
    {
        return [
            self::PROPERTY => [
                'property' => self::PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }

    /** {@inheritDoc} */
    protected function extractProperties(Request $request): array
    {
        $properties = $request->query->all();

        if (!array_key_exists(self::PROPERTY, $properties)) {
            $properties[self::PROPERTY] = self::PUBLIC;
        }

        return $properties;
    }

    /** {@inheritDoc} */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $property = strtolower($property);
        if ($property !== self::PROPERTY) {
            return;
        }

        $value = strtolower($value);
        if (!in_array($value, self::ALLOWED_VALUES)) {
            throw new InvalidArgumentException('Allowed values for "access" are "public", "private", "all".');
        }

        if (self::ALL === $value) {
            return;
        }

        $alias = 'o';
        $valueParameter = $queryNameGenerator->generateParameterName($property);
        $hasPublicAccess = $this->propertyValueToBoolean($value);
        if (Event::class === $resourceClass) {
            $queryBuilder
                ->andWhere(sprintf('%s.hasPublicAccess = :%s', $alias, $valueParameter))
                ->setParameter($valueParameter, $hasPublicAccess);
        } elseif (Occurrence::class === $resourceClass || DailyOccurrence::class === $resourceClass) {
            $alias = 'event';
            $queryBuilder->join('o.event', $alias);
            $queryBuilder
                ->andWhere(sprintf('%s.hasPublicAccess = :%s', $alias, $valueParameter))
                ->setParameter($valueParameter, $hasPublicAccess);
        }
    }

    /**
     * Convert the given "access" value to boolean
     *
     * @param string $value
     * @return bool
     */
    private function propertyValueToBoolean(string $value): bool
    {
        switch ($value) {
            case self::PUBLIC:
                return true;
            case self::PRIVATE:
                return false;
            default:
                throw new InvalidArgumentException('Allowed values for "access" are "public", "private", "all".');
        }
    }
}
