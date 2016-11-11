<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class GroupRepository extends EntityRepository {
  /**
   * Find groups by id or name.
   *
   * @param array $ids
   */
  public function findByIds(array $ids) {
    return $this->getEntityManager()
      ->createQuery(
        'SELECT g FROM AppBundle:Group g WHERE g.id IN (:ids) OR g.name IN (:ids)'
      )
      ->setParameter('ids', $ids)
      ->getResult();
  }

  public function findByUserIds(array $ids) {
    return $this->getEntityManager()
      ->createQuery(
        'SELECT g FROM AppBundle:Group g JOIN g.users u WHERE u.id IN (:ids) OR u.username IN (:ids)'
      )
      ->setParameter('ids', $ids)
      ->getResult();
  }
}
