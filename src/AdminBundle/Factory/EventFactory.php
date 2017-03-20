<?php

namespace AdminBundle\Factory;

use AdminBundle\Entity\Feed;
use AppBundle\Entity\Entity;
use AppBundle\Entity\Event;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 */
class EventFactory extends EntityFactory {
  /**
   * @var Feed
   */
  protected $feed;

  /**
   * @var OrganizerFactory
   */
  protected $organizerFactory;

  /**
   * @var OccurrenceFactory
   */
  protected $occurrenceFactory;

  /**
   * @param \AdminBundle\Entity\Feed $feed
   */
  public function setFeed(Feed $feed) {
    $this->feed = $feed;
    if ($this->valueConverter) {
      $this->valueConverter->setFeed($feed);
    }
  }

  public function setOrganizerFactory(OrganizerFactory $organizerFactory) {
    $this->organizerFactory = $organizerFactory;
  }

  /**
   * @param \AdminBundle\Factory\OccurrenceFactory $occurrenceFactory
   */
  public function setOccurrenceFactory(OccurrenceFactory $occurrenceFactory) {
    $this->occurrenceFactory = $occurrenceFactory;
  }

  /**
   * @param array $data
   * @return \AppBundle\Entity\Event|object
   */
  public function get(array $data) {
    $entity = $this->getEntity($data);
    if ($entity) {
      $this->setValues($entity, $data);
      $this->persist($entity);
      $this->flush();
    }

    return $entity;
  }

  /**
   * @param array $data
   * @return \AppBundle\Entity\Event|object
   */
  private function getEntity(array $data) {
    $feed = isset($data['feed']) ? $data['feed'] : NULL;
    $feedEventId = isset($data['feed_event_id']) ? $data['feed_event_id'] : NULL;
    $id = isset($data['id']) ? $data['id'] : uniqid();

    if (!$feedEventId) {
      return NULL;
    }

    // An event may have been (soft) deleted. We want to reuse it.
    $hasSoftdeleteable = FALSE;
    $filters = $this->em->getFilters();
    if ($filters->has('softdeleteable')) {
      $hasSoftdeleteable = TRUE;
      $filters->disable('softdeleteable');
    }

    $event = $this->em->getRepository('AppBundle:Event')->findOneBy([
      'feed' => $feed,
      'feedEventId' => $feedEventId,
    ]);

    if ($event === NULL) {
      $event = new Event();
      $event->setFeedEventId($id);
    }

    if ($hasSoftdeleteable) {
      $event->setDeletedAt(NULL);
      $filters->enable('softdeleteable');
    }

    return $event;
  }

  /**
   * @param \AppBundle\Entity\Entity $entity
   * @param $key
   * @param $value
   */
  protected function setValue(Entity $entity, $key, $value) {
    if ($entity instanceof Event) {
      if ($this->accessor->isWritable($entity, $key)) {
        switch ($key) {
          case 'occurrences':
            $occurrences = new ArrayCollection();
            foreach ($value as $item) {
              $item['event'] = $entity;
              $occurrence = $this->occurrenceFactory->get($item);
              $occurrences->add($occurrence);
            }
            $entity->setOccurrences($occurrences);
            return;
          case 'organizer':
            $organizer = $this->organizerFactory->get($value);
            $entity->setOrganizer($organizer);
            return;
        }
      }
    }

    parent::setValue($entity, $key, $value);
  }

}
