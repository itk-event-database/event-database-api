<?php

namespace AdminBundle\Service;

use AdminBundle\Entity\Feed;
use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;
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

    foreach ($events as $event) {
      $event->getOccurrences()->clear();
      $this->em->persist($event);
      $this->em->remove($event);
    }
    $this->em->flush();
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
