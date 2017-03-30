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

  /**
   * @param Feed $feed
   *
   * Get data from a feed.
   *
   * @return array
   */
  public function getEvents(Feed $feed) {

  }

  /**
   * @param Feed $feed
   *
   * Validate data in a feed.
   */
  public function validate(Feed $feed) {
    $data = $this->getEvents();

  }

}
