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
use AppBundle\Entity\Place;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class GeolocationFilter extends AbstractFilter
{
    private $property = 'geolocation';
    private $alias = 'geolocation';
    // Radius in km (cf. https://www.plumislandmedia.net/mysql/haversine-mysql-nearest-loc/)
    private $radius = [
        'default' => 10,
        'min' => 1,
    ];

    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack, LoggerInterface $logger = null, array $properties = null)
    {
        $properties += [
            'property' => 'geolocation',
            'alias' => 'geolocation',
            'lat' => 'lat',
            'lng' => 'lng',
            'radius' => $this->radius,
        ];

        if (!isset($properties['radius']['default'])) {
            $properties['radius']['default'] = $this->radius['default'];
        }
        if (!isset($properties['radius']['min'])) {
            $properties['radius']['min'] = $this->radius['min'];
        }
        parent::__construct($managerRegistry, $requestStack, $logger, $properties);
        $this->property = $this->properties['property'];
        $this->alias = $this->properties['alias'];
        $this->radius = $this->properties['radius'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            sprintf('%s[%s]', $this->property, 'origin') => [
                'property' => $this->property,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Origin of search; format: «latitude»,«longitude», e.g. 56.1535557,10.2120222. Alternatively, use “lat” and “lng”.',
                    'type' => 'string',
                ],
            ],
            sprintf('%s[%s]', $this->property, 'lat') => [
                'property' => $this->property,
                'type' => 'number',
                'required' => false,
                'swagger' => [
                    'description' => 'Latitude of origin of search. Specify both “lat” and “lng” in query or use “origin”.',
                    'type' => 'number',
                ],
            ],
            sprintf('%s[%s]', $this->property, 'lng') => [
                'property' => $this->property,
                'type' => 'number',
                'required' => false,
                'swagger' => [
                    'description' => 'Longitude of origin of search. Specify both “lat” and “lng” in query or use “origin”.',
                    'type' => 'number',
                ],
            ],
            sprintf('%s[%s]', $this->property, 'radius') => [
                'property' => $this->property,
                'type' => 'number',
                'required' => false,
                'swagger' => [
                    'description' => sprintf('Search radius in km (default: %0.2f; min: %0.2f)', $this->radius['default'], $this->radius['min']),
                    'type' => 'number',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($property !== $this->alias || !is_array($value)) {
            return;
        }

        if (isset($value['lat'], $value['lng'])) {
            $lat = $value['lat'];
            $lng = $value['lng'];
        } elseif (isset($value['origin'])) {
            list($lat, $lng) = explode(',', $value['origin']);
        } else {
            return;
        }

        $radius = max((int) (isset($value['radius']) ? $value['radius'] : $this->radius['default']), $this->radius['min']);

        $resource = new $resourceClass();
        if (!$resource instanceof Place) {
            return;
        }

        // @see https://www.plumislandmedia.net/mysql/haversine-mysql-nearest-loc/
        $alias = 'o';
        $latParameter = $queryNameGenerator->generateParameterName($this->properties['lat']);
        $cosRadLatParameter = $queryNameGenerator->generateParameterName('cosRadLatParameter');
        $lngParameter = $queryNameGenerator->generateParameterName($this->properties['lng']);
        $radiusParameter = $queryNameGenerator->generateParameterName('radius');
        $queryBuilder
            ->andWhere(sprintf('%s.%s is not null', $alias, $this->properties['lat']))
            ->andWhere(sprintf('%s.%s is not null', $alias, $this->properties['lng']))
            ->andWhere(sprintf(
                '%s.%s between :%s - (:%s / 111.045) and :%s + (:%s / 111.045)',
                $alias,
                $this->properties['lat'],
                $latParameter,
                $radiusParameter,
                $latParameter,
                $radiusParameter
            ))
            ->andWhere(sprintf(
                '%s.%s between :%s - (:%s / (111.045 * :%s)) and :%s + (:%s / (111.045 * :%s))',
                $alias,
                $this->properties['lng'],
                $lngParameter,
                $radiusParameter,
                $cosRadLatParameter,
                $lngParameter,
                $radiusParameter,
                $cosRadLatParameter
            ))
            ->setParameter($latParameter, $lat)
            ->setParameter($cosRadLatParameter, cos(deg2rad($lat)))
            ->setParameter($lngParameter, $lng)
            ->setParameter($radiusParameter, $radius);
    }
}
