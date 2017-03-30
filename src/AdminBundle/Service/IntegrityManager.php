<?php

namespace AdminBundle\Service;

use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;
use AppBundle\Entity\Organizer;
use AppBundle\Entity\Place;
use AppBundle\Entity\Tag;
use AppBundle\Entity\Tagging;
use Doctrine\ORM\EntityManagerInterface;

class IntegrityManager {
  /**
   * @var \Doctrine\ORM\EntityManagerInterface
   */
  private $entityManager;

  public function __construct(EntityManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
  }

  /**
   * @param $entity
   *
   * @return boolean|string
   *   Return true iff entity can be safely deleted. Otherwise, return a message telling why it cannot be deleted.
   */
  public function canDelete($entity) {
    if ($entity instanceof Organizer) {
      return $this->canDeleteOrganizer($entity);
    }
    elseif ($entity instanceof Place) {
      return $this->canDeletePlace($entity);
    }
    elseif ($entity instanceof Tag) {
      return $this->canDeleteTag($entity);
    }

    return TRUE;
  }

  private function canDeleteOrganizer(Organizer $organizer) {
    $queryBuilder = $this->entityManager->createQueryBuilder();
    $queryBuilder
      ->select($queryBuilder->expr()->count('e.id'))
      ->from(Event::class, 'e')
      ->where('e.organizer = :organizer')
      ->setParameter('organizer', $organizer);
    $count = (int) $queryBuilder->getQuery()->getSingleScalarResult();

    if ($count === 0) {
      return TRUE;
    }

    return [
      'message' => 'Organizer is used by %count% events',
      'arguments' => ['%count%' => $count],
    ];
  }

  private function canDeletePlace(Place $place) {
    $queryBuilder = $this->entityManager->createQueryBuilder();
    $queryBuilder
      ->select($queryBuilder->expr()->count('e.id'))
      ->from(Occurrence::class, 'e')
      ->where('e.place = :place')
      ->setParameter('place', $place);
    $count = (int) $queryBuilder->getQuery()->getSingleScalarResult();

    if ($count === 0) {
      return TRUE;
    }

    return [
      'message' => 'Place is used by %count% occurrences',
      'arguments' => ['%count%' => $count],
    ];
  }

  private function canDeleteTag(Tag $tag) {
    $queryBuilder = $this->entityManager->createQueryBuilder();
    $queryBuilder
      ->select($queryBuilder->expr()->count('e.id'))
      ->from(Tagging::class, 'e')
      ->where('e.tag = :tag')
      ->setParameter('tag', $tag);
    $count = (int) $queryBuilder->getQuery()->getSingleScalarResult();

    if ($count === 0) {
      return TRUE;
    }

    return [
      'message' => 'Tag is used by %count% objects',
      'arguments' => ['%count%' => $count],
    ];
  }

}
