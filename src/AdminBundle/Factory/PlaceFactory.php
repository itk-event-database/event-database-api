<?php

namespace AdminBundle\Factory;

use AppBundle\Entity\Place;
use AppBundle\Entity\User;

/**
 *
 */
class PlaceFactory extends EntityFactory {
  protected $user;

  /**
   * @param \AppBundle\Entity\User $user
   */
  public function setUser(User $user) {
    $this->user = $user;
  }

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

  /**
   *
   */
  private function getUser() {
    if ($this->user) {
      return $this->user;
    }

    $token = $this->container->get('security.token_storage')->getToken();

    return $token ? $token->getUser() : NULL;
  }

}
