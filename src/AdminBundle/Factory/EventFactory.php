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
    $this->setValues($entity, $data);
    $this->persist($entity);
    $this->flush();

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

    $event = $this->em->getRepository('AppBundle:Event')->findOneBy([
      'feed' => $feed,
      'feedEventId' => $feedEventId,
    ]);

    if ($event === NULL) {
      $event = new Event();
      $event->setFeedEventId($id);
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
        }
      }
    }

    parent::setValue($entity, $key, $value);
  }

}
