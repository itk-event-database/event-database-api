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
    $query = [
      'feed' => $this->feed,
      'name' => $name,
    ];
    $place = $this->em->getRepository('AppBundle:Place')->findOneBy($query);
    if ($place === null) {
      $place = new Place();
      $place->setFeed($this->feed);
    }

    return $place;
  }
}