<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\EventListener;

use AppBundle\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use DoctrineExtensions\Taggable\Taggable;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class PostQueryBuilderSubscriber implements EventSubscriberInterface
{
    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::POST_LIST_QUERY_BUILDER => ['onPostListQueryBuilder'],
            EasyAdminEvents::POST_SEARCH_QUERY_BUILDER => ['onPostSearchQueryBuilder'],
        ];
    }

    /**
     * Called on POST_LIST_QUERY_BUILDER event.
     *
     * @param GenericEvent $event
     */
    public function onPostListQueryBuilder(GenericEvent $event)
    {
        $queryBuilder = $event->getArgument('query_builder');
        if ($event->hasArgument('request')) {
            $this->applyRequestFilters($queryBuilder, $event->getArgument('request')->get('tags', []));
        }
    }

    /**
     * Called on POST_SEARCH_QUERY_BUILDER event.
     *
     * @param GenericEvent $event
     */
    public function onPostSearchQueryBuilder(GenericEvent $event)
    {
        $this->onPostListQueryBuilder($event);
    }

    /**
     * Applies filters on queryBuilder.
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $tagNames
     */
    protected function applyRequestFilters(QueryBuilder $queryBuilder, array $tagNames = [])
    {
        if (empty($tagNames)) {
            return;
        }

        $entityClass = $queryBuilder->getRootEntities()[0];

        if (!is_subclass_of($entityClass, Taggable::class)) {
            return null;
        }

        $taggableType = (new $entityClass())->getTaggableType();
        $field = $queryBuilder->getRootAliases()[0].'.id';

        $tagRepo = $this->entityManager->getRepository(Tag::class);
        foreach ($tagNames as $index => $tagName) {
            $tagIds = $tagRepo->getResourceIdsForTag($taggableType, $tagName);
            $parameterName = 'tagged_item_ids_'.$index;
            $queryBuilder
                ->andWhere($field.' in (:'.$parameterName.')')
                ->setParameter($parameterName, $tagIds ?? [0]);
        }
    }

    /**
     * Filters queryBuilder.
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $field
     * @param string       $parameter
     * @param mixed        $value
     */
    protected function filterQueryBuilder(QueryBuilder $queryBuilder, string $field, string $parameter, $value)
    {
        // For multiple value, use an IN clause, equality otherwise
        if (is_array($value)) {
            $filterDqlPart = $field.' IN (:'.$parameter.')';
        } elseif ('_NULL' === $value) {
            $parameter = null;
            $filterDqlPart = $field.' IS NULL';
        } elseif ('_NOT_NULL' === $value) {
            $parameter = null;
            $filterDqlPart = $field.' IS NOT NULL';
        } else {
            $filterDqlPart = $field.' = :'.$parameter;
        }
        $queryBuilder->andWhere($filterDqlPart);
        if (null !== $parameter) {
            $queryBuilder->setParameter($parameter, $value);
        }
    }

    /**
     * Checks if filter is directly appliable on queryBuilder.
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $field
     *
     * @return bool
     */
    protected function isFilterAppliable(QueryBuilder $queryBuilder, string $field): bool
    {
        $qbClone = clone $queryBuilder;

        try {
            $qbClone->andWhere($field.' IS NULL');
            // Generating SQL throws a QueryException if using wrong field/association
            $qbClone->getQuery()->getSQL();
        } catch (QueryException $e) {
            return false;
        }

        return true;
    }
}
