<?php

namespace AdminBundle\Factory;

use AppBundle\Entity\Place;

/**
 *
 */
class PlaceFactory extends EntityFactory {
  /**
   * @param array $data
   * @return \AppBundle\Entity\Place|object
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
   * @return \AppBundle\Entity\Place|object
   */
  private function getEntity(array $data) {
    $name = $data['name'];
    $user = $this->getUser();
    $query = [
      'createdBy' => $user,
      'name' => $name,
    ];
    $place = $this->em->getRepository('AppBundle:Place')->findOneBy($query);
    if ($place === NULL) {
      $place = new Place();
      // We need to explicitly set createdBy to make the findByOne query above
      // work. (Caching issue?)
      $place
        ->setCreatedBy($user)
        ->setUpdatedBy($user);
    }

    return $place;
  }
}
