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
    $entity = $this->findExisting($data);
    if (!$entity) {
      $entity = $this->create($data);
    }

    return $entity;
  }

  private function create(array $data) {
    $entity = new Place();
    $user = $this->getUser();
    $entity->setCreatedBy($user);
    $this->setValues($entity, $data);
    $this->persist($entity);
    $this->flush();

    return $entity;
  }

  /**
   * @param array $data
   * @return \AppBundle\Entity\Place|object
   */
  private function findExisting(array $data) {
    // Try to find existing place by
    //   1. url
    //   2. email
    //   3. name
    $keys = ['url', 'email', 'name'];
    $repository = $this->em->getRepository(Place::class);

    foreach ($keys as $key) {
      if (isset($data[$key])) {
        $place = $repository->findOneBy([$key => $data[$key]]);
        if ($place) {
          return $place;
        }
      }
    }

    return NULL;
  }
}
