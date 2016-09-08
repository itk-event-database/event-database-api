<?php

namespace AdminBundle\Factory;

use AdminBundle\Entity\Feed;
use AppBundle\Entity\Entity;
use AppBundle\Entity\Event;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class EventFactory extends EntityFactory {
  protected $feed;
  protected $occurrenceFactory;

  public function setFeed(Feed $feed) {
    $this->feed = $feed;
    if ($this->valueConverter) {
      $this->valueConverter->setFeed($feed);
    }
  }

  public function setOccurrenceFactory(OccurrenceFactory $occurrenceFactory) {
    $this->occurrenceFactory = $occurrenceFactory;
  }

  public function get(array $data) {
    $entity = $this->getEntity($data);
    $this->setValues($entity, $data);
    $this->persist($entity);
    $this->flush();

    return $entity;
  }

  private function getEntity(array $data) {
    $feed = isset($data['feed']) ? $data['feed'] : null;
    $feedEventId = isset($data['feed_event_id']) ? $data['feed_event_id'] : null;
    $id = isset($data['id']) ? $data['id'] : uniqid();

    $event = $this->em->getRepository('AppBundle:Event')->findOneBy([
      'feed' => $feed,
      'feedEventId' => $feedEventId,
    ]);

    if ($event === null) {
      $event = new Event();
      $event->setFeedEventId($id);
    }

    return $event;
  }

  protected function setValue(Entity $entity, $key, $value, PropertyAccessor $accessor) {
    if ($accessor->isWritable($entity, $key)) {
      switch ($key) {
        case 'id':
          return;
        case 'tags':
          if ($this->tagManager) {
            $tags = $this->tagManager->loadOrCreateTags(array_map('strtolower', $value));
            $this->tagManager->addTags($tags, $entity);
          }
          return;

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

    parent::setValue($entity, $key, $value, $accessor);
  }

}