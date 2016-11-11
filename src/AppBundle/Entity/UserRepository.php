<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository {
  /**
   * Find users by id or username.
   *
   * @param array $ids
   */
  public function findByIds(array $ids) {
    return $this->getEntityManager()
      ->createQuery(
        'SELECT u FROM AppBundle:User u WHERE u.id IN (:ids) OR u.username IN (:ids)'
      )
      ->setParameter('ids', $ids)
      ->getResult();
  }
}
