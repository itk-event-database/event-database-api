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
use AppBundle\Entity\DailyOccurrence;
use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class PublishedFilter extends AbstractFilter
{
    private $property = 'published';

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (Event::class !== $resourceClass && Occurrence::class !== $resourceClass && DailyOccurrence::class !== $resourceClass) {
            return false;
        }

        return parent::apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
        'published' => [
        'property' => 'published',
        'type' => 'boolean',
        'required' => false,
        ],
        ];
    }

    protected function extractProperties(Request $request): array
    {
        $properties = $request->query->all();

        if (!array_key_exists($this->property, $properties)) {
            $properties[$this->property] = true;
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($property !== $this->property) {
            return;
        }

        $value = 0 !== strcasecmp($value, 'false');
        $alias = 'o';
        $valueParameter = $queryNameGenerator->generateParameterName($property);
        if (Event::class === $resourceClass) {
            $queryBuilder
                ->andWhere(sprintf('%s.isPublished = :%s', $alias, $valueParameter))
                ->setParameter($valueParameter, $value ? 1 : 0);
        } elseif (Occurrence::class === $resourceClass || DailyOccurrence::class === $resourceClass) {
            $alias = 'e';
            $queryBuilder->join('o.event', $alias);
            $queryBuilder
                ->andWhere(sprintf('%s.isPublished = :%s', $alias, $valueParameter))
                ->setParameter($valueParameter, $value ? 1 : 0);
        }
    }
}
