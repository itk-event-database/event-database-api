<?php

namespace AppBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class PublishedFilter extends AbstractFilter
{
    private $property = 'published';

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (Event::class !== $resourceClass && Occurrence::class !== $resourceClass) {
            return false;
        }

        return parent::apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName);
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

        $value = strcasecmp($value, 'false') !== 0;
        $alias = 'o';
        $valueParameter = $queryNameGenerator->generateParameterName($property);
        if (Event::class == $resourceClass) {
            $queryBuilder
                ->andWhere(sprintf('%s.isPublished = :%s', $alias, $valueParameter))
                ->setParameter($valueParameter, $value ? 1 : 0);
        } elseif (Occurrence::class === $resourceClass) {
            $alias = 'e';
            $queryBuilder->join('o.event', $alias);
            $queryBuilder
                ->andWhere(sprintf('%s.isPublished = :%s', $alias, $valueParameter))
                ->setParameter($valueParameter, $value ? 1 : 0);
        }
    }

  /**
   * {@inheritdoc}
   */
    public function getDescription(string $resourceClass) : array
    {
        return [
        'published' => [
        'property' => 'published',
        'type' => 'boolean',
        'required' => false,
        ]
        ];
    }
}
