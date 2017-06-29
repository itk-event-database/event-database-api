<?php

namespace AppBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
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
    private $name;

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
        $this->name = $name;
    }

  /**
   * {@inheritdoc}
   */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (null === ($request = $this->requestStack->getCurrentRequest())) {
            return;
        }

        $resource = new $resourceClass();
        if (!$resource instanceof Taggable) {
            return;
        }

        $taggableType = $resource->getTaggableType();

        $ids = null;
        foreach ($this->extractProperties($request) as $property => $values) {
            if ($property == $this->name) {
                $tagNames = $this->tagManager->splitTagNames($values);
                foreach ($tagNames as $tagName) {
                    // $tagRepo = $this->managerRegistry->getManager()->getRepository('DoctrineExtensions\Taggable\Entity\Tag');
                    $tagRepo = $this->managerRegistry->getManager()->getRepository('AppBundle\Entity\Tag');
                    $tagIds = $tagRepo->getResourceIdsForTag($taggableType, $tagName);
                    if ($ids === null) {
                        $ids = $tagIds;
                    } else {
                        $ids = array_intersect($ids, $tagIds);
                    }
                }

                $alias = 'o';
                $valueParameter = $queryNameGenerator->generateParameterName($property);
                $queryBuilder
                ->andWhere(sprintf('%s.id IN (:%s)', $alias, $valueParameter))
                ->setParameter($valueParameter, $ids);
            }
        }
    }

  /**
   * {@inheritdoc}
   */
    public function getDescription(string $resourceClass) : array
    {
        return [
        'tags' => [
          'property' => $this->name,
          'type' => 'string',
          'required' => false,
        ]
        ];
    }
}
