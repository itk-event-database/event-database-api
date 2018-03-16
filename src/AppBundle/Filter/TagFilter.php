<?php

namespace AppBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;
use AppBundle\Entity\Tag;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use DoctrineExtensions\Taggable\TagManager;
use Symfony\Component\HttpFoundation\RequestStack;
use DoctrineExtensions\Taggable\Taggable;

/**
 *
 */
class TagFilter extends AbstractFilter
{
    private $tagManager;
    private $property;

  /**
   * @param ManagerRegistry $managerRegistry
   * @param RequestStack $requestStack
   * @param \DoctrineExtensions\Taggable\TagManager $tagManager
   * @param string $name
   * @internal param array|null $properties
   */
    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack, TagManager $tagManager, string $name)
    {
        parent::__construct($managerRegistry, $requestStack, null);

        $this->tagManager = $tagManager;
        $this->property = $name;
    }

  /**
   * {@inheritdoc}
   */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (null === ($request = $this->requestStack->getCurrentRequest())) {
            return;
        }

        $resource = $resourceClass === Occurrence::class ? new Event() : new $resourceClass();
        if (!$resource instanceof Taggable) {
            return;
        }

        $taggableType = $resource->getTaggableType();

        $ids = null;
        foreach ($this->extractProperties($request) as $property => $values) {
            if ($property === $this->property) {
                $intersect = true;
                if (is_array($values)) {
                    $tagNames = $values;
                    $intersect = false;
                } else {
                    $tagNames = $this->tagManager->splitTagNames($values);
                }
                foreach ($tagNames as $tagName) {
                    $tagRepo = $this->managerRegistry->getManager()->getRepository(Tag::class);
                    $tagIds = $tagRepo->getResourceIdsForTag($taggableType, $tagName);
                    if ($ids === null) {
                        $ids = $tagIds;
                    } elseif ($intersect) {
                        $ids = array_intersect($ids, $tagIds);
                    } else {
                        $ids = array_merge($ids, $tagIds);
                    }
                }

                if ($resourceClass === Occurrence::class) {
                    $alias = 'o';
                    $valueParameter = $queryNameGenerator->generateParameterName($property);
                    $queryBuilder
                        ->join($alias.'.event', 'occurrence_event')
                        ->andWhere(sprintf('occurrence_event.id IN (:%s)', $valueParameter))
                        ->setParameter($valueParameter, $ids);
                } else {
                    $alias = 'o';
                    $valueParameter = $queryNameGenerator->generateParameterName($property);
                    $queryBuilder
                        ->andWhere(sprintf('%s.id IN (:%s)', $alias, $valueParameter))
                        ->setParameter($valueParameter, $ids);
                }
            }
        }
    }

  /**
   * {@inheritdoc}
   */
    public function getDescription(string $resourceClass) : array
    {
        $description = [];
        $property = $this->property;

        $filterParameterNames = [
            $property,
            $property.'[]',
        ];

        foreach ($filterParameterNames as $filterParameterName) {
            $description[$filterParameterName] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'strategy' => SearchFilter::STRATEGY_EXACT,
            ];
        }

        return $description;
    }
}
