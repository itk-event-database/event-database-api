<?php

namespace AdminBundle\Service;

use AdminBundle\Entity\Feed;
use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;

class FeedManager {
  private $em;

  public function __construct(EntityManagerInterface $entityManager) {
    $this->em = $entityManager;
  }

  public function enable(Feed $feed) {
    $feed->setEnabled(TRUE);
    $this->em->persist($feed);
    $this->em->flush();

    return TRUE;
  }

  public function disable(Feed $feed) {
    $this->removeEvents($feed);

    $feed->setEnabled(FALSE);
    $this->em->persist($feed);
    $this->em->flush();

    return TRUE;
  }

  /**
   * @param Feed $feed
   *
   * Remove all events imported from a feed.
   */
  public function removeEvents(Feed $feed) {
    $repository = $this->em->getRepository(Event::class);
    $events = $repository->findBy(['feed' => $feed]);

    // Note: We bypass all voters and stuff when deleting feed events.
    // Delete occurrences.
    $qb = $this->em->createQueryBuilder();
    $query = $qb->delete(Occurrence::class, 'e')
      ->where('e.event in (:events)')
      ->setParameter('events', $events)
      ->getQuery();
    $query->execute();

    // (Soft-)delete events.
    $qb = $this->em->createQueryBuilder();
    $query = $qb->update(Event::class, 'e')
      ->set('e.deletedAt', ':deletedAt')
      ->where('e.id in (:events)')
      ->setParameter('events', $events)
      ->setParameter('deletedAt', new \DateTime(), Type::DATETIME)
      ->getQuery();
    $query->execute();
  }

  const FEED_CLEAN_UP_FUTURE = 'FEED_CLEAN_UP_FUTURE';
  const FEED_CLEAN_UP_ALL = 'FEED_CLEAN_UP_ALL';

  /**
   * Get events (indexed by id).
   *
   * @param \AdminBundle\Entity\Feed $feed
   * @param string $strategy
   * @return array|null
   */
  public function getCleanUpEvents(Feed $feed, string $strategy) {
    $connection = $this->em->getConnection();
    $eventIds = NULL;

    switch ($strategy) {
      case self::FEED_CLEAN_UP_FUTURE:
        // Get all feed events from with future occurrences …
        $sql = 'select e.id from event e join occurrence o on o.event_id = e.id where e.feed_id = :feed_id and o.end_date >= :now';
        // … plus events with no occurrences.
        $sql .= ' union select e.id from event e where e.feed_id = :feed_id and e.id not in (select event_id from occurrence)';
        $stmt = $connection->prepare($sql);
        $stmt->execute([
          'feed_id' => $feed->getId(),
          'now' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
        $eventIds = array_map(function ($row) {
          return (int)$row['id'];
        }, $stmt->fetchAll());
        break;
      case self::FEED_CLEAN_UP_ALL:
        // Get all feed events.
        $sql = 'select e.id from event e where e.feed_id = :feed_id';
        $stmt = $connection->prepare($sql);
        $stmt->execute([
          'feed_id' => $feed->getId(),
        ]);
        $eventIds = array_map(function ($row) {
          return (int)$row['id'];
        }, $stmt->fetchAll());
        break;
    }

    return $eventIds ? array_combine($eventIds, $eventIds) : NULL;
  }

  /**
   * Clean up (i.e. delete) some events.
   *
   * @param \AdminBundle\Entity\Feed $feed
   * @param array|NULL $eventIds
   *   A result of calling getEventIds
   */
  public function cleanUpEvents(Feed $feed, array $eventIds = NULL) {
    if ($eventIds) {
      $repository = $this->em->getRepository(Event::class);
      $events = $repository->findBy(['id' => array_keys($eventIds)]);

      // Note: We bypass all voters and stuff when deleting feed events.
      // Delete occurrences.
      $qb = $this->em->createQueryBuilder();
      $query = $qb->delete(Occurrence::class, 'e')
        ->where('e.event in (:events)')
        ->setParameter('events', $events)
          ->getQuery();
      $query->execute();

      // (Soft-)delete events.
      $qb = $this->em->createQueryBuilder();
      $query = $qb->update(Event::class, 'e')
        ->set('e.deletedAt', ':deletedAt')
        ->where('e.id in (:events)')
        ->setParameter('events', $events)
        ->setParameter('deletedAt', new \DateTime(), Type::DATETIME)
        ->getQuery();
      $query->execute();
    }
  }

}
