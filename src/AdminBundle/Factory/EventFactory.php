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
    // if (isset($data['image'])) {
    //   $data['originalImage'] = $data['image'];
    //   $data['image'] = $this->converter->downloadImage($data['image']);
    // }

    $entity = $this->getEntity($data);
    $this->setValues($entity, $data);
    $this->persist($entity);
    $this->flush();

    return $entity;
  }

  private function getEntity(array $data) {
    $id = isset($data['id']) ? $data['id'] : uniqid();

    $query = $this->em->createQuery('SELECT e FROM AppBundle:Event e WHERE e.feedEventId = :feedEventId AND e.feed = :feed');
    $query->setParameter('feed', $this->feed);
    $query->setParameter('feedEventId', $id);

    $event = $query->getOneOrNullResult();
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