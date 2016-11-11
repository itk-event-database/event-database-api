<?php

namespace AppBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Blameable\Blameable;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class OwnerFilter extends AbstractFilter {
  /**
   * @param ManagerRegistry $managerRegistry
   * @param RequestStack $requestStack
   * @param array $configuration
   */
  public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack) {
    parent::__construct($managerRegistry, $requestStack);
  }

  /**
   * {@inheritdoc}
   */
  protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = NULL) {
    if (!in_array($property, ['user', 'group', 'editable_by'])) {
      return;
    }

    $resource = new $resourceClass();
    if (!$resource instanceof Blameable) {
      return;
    }

    $ids = preg_split('/\s*,\s*/', $value, null, PREG_SPLIT_NO_EMPTY);
    $users = null;
    if ($property === 'user') {
      $users = $this->managerRegistry->getRepository('AppBundle:User')->findByIds($ids);
    }
    elseif ($property === 'group') {
      $groups = $this->managerRegistry->getRepository('AppBundle:Group')->findByIds($ids);
      $users = [];
      foreach ($groups as $group) {
        $users = array_merge($users, $group->getUsers()->toArray());
      }
    }
    elseif ($property === 'editable_by') {
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

  /**
   * {@inheritdoc}
   */
  public function getDescription(string $resourceClass) : array {
    return [
      'user' => [
        'property' => 'created_by',
        'type' => 'string',
        'required' => FALSE,
      ],
      'group' => [
        'property' => 'created_by',
        'type' => 'string',
        'required' => FALSE,
      ],
    ];
  }
}
