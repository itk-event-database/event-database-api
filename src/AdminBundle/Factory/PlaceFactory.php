<?php

namespace AdminBundle\Factory;

use AppBundle\Entity\Place;

class PlaceFactory extends EntityFactory {
  public function get(array $data) {
    $entity = $this->getEntity($data);
    $this->setValues($entity, $data);
    $this->persist($entity);
    $this->flush();

    return $entity;
  }

  private function getEntity(array $data) {
    $name = $data['name'];
    $query = $this->em->createQuery('SELECT p FROM AppBundle:Place p WHERE p.feed = :feed AND p.name = :name');
    $query->setParameter('feed', $this->feed);
    $query->setParameter('name', $name);

    $place = $query->getOneOrNullResult();
    if ($place === null) {
      $place = new Place();
      $place->setFeed($this->feed);
    }

    return $place;
  }
}