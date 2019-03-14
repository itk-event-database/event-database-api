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
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;
use AppBundle\Entity\OccurrenceTrait;
use AppBundle\Entity\Tag;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use DoctrineExtensions\Taggable\Taggable;
use DoctrineExtensions\Taggable\TagManager;
use Symfony\Component\HttpFoundation\RequestStack;

class TagFilter extends AbstractFilter
{
    private $tagManager;
    private $property;

    /**
     * @param ManagerRegistry                         $managerRegistry
     * @param RequestStack                            $requestStack
     * @param \DoctrineExtensions\Taggable\TagManager $tagManager
     * @param string                                  $name
     *
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
    public function getDescription(string $resourceClass): array
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

    /**
     * {@inheritdoc}
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (null === ($request = $this->requestStack->getCurrentRequest())) {
            return;
        }

        // @TODO check for actual event relation
        $hasEventRelation = in_array(OccurrenceTrait::class, class_uses($resourceClass), true);

        $resource = $hasEventRelation ? new Event() : new $resourceClass();
        if (!$resource instanceof Taggable) {
            return;
        }

        $taggableType = $resource->getTaggableType();

        if (str_replace('_', '.', $property) === $this->property) {
            $intersect = true;
            if (is_array($value)) {
                $tagNames = $value;
                $intersect = false;
            } else {
                $tagNames = $this->tagManager->splitTagNames($value);
            }
            $ids = null;
            foreach ($tagNames as $tagName) {
                $tagRepo = $this->managerRegistry->getManager()->getRepository(Tag::class);
                $tagIds = $tagRepo->getResourceIdsForTag($taggableType, $tagName);
                if (null === $ids) {
                    $ids = $tagIds;
                } elseif ($intersect) {
                    $ids = array_intersect($ids, $tagIds);
                } else {
                    $ids = array_merge($ids, $tagIds);
                }
            }

            if ($hasEventRelation) {
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
