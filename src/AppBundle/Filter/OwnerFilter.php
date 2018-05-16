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
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Blameable\Blameable;
use Symfony\Component\HttpFoundation\RequestStack;

class OwnerFilter extends AbstractFilter
{
    /**
     * @param ManagerRegistry $managerRegistry
     * @param RequestStack    $requestStack
     * @param array           $configuration
     */
    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack)
    {
        parent::__construct($managerRegistry, $requestStack);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
        'user' => [
        'property' => 'created_by',
        'type' => 'string',
        'required' => false,
        ],
        'group' => [
        'property' => 'created_by',
        'type' => 'string',
        'required' => false,
        ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (!in_array($property, ['user', 'group', 'editable_by'], true)) {
            return;
        }

        $resource = new $resourceClass();
        if (!$resource instanceof Blameable) {
            return;
        }

        $ids = preg_split('/\s*,\s*/', $value, null, PREG_SPLIT_NO_EMPTY);
        $users = null;
        if ('user' === $property) {
            $users = $this->managerRegistry->getRepository('AppBundle:User')->findByIds($ids);
        } elseif ('group' === $property) {
            $groups = $this->managerRegistry->getRepository('AppBundle:Group')->findByIds($ids);
            $users = [];
            foreach ($groups as $group) {
                $users = array_merge($users, $group->getUsers()->toArray());
            }
        } elseif ('editable_by' === $property) {
            $users = $this->managerRegistry->getRepository('AppBundle:User')->findByIds($ids);
            $groups = $this->managerRegistry->getRepository('AppBundle:Group')->findByUserIds($ids);
            foreach ($groups as $group) {
                $users = array_merge($users, $group->getUsers()->toArray());
            }
        }

        $alias = 'o';
        $valueParameter = $queryNameGenerator->generateParameterName($property);
        $queryBuilder->andWhere(sprintf('%s.createdBy IN (:%s)', $alias, $valueParameter))
        ->setParameter($valueParameter, $users);
    }
}
