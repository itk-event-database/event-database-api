<?php

namespace AdminBundle\Factory;

use AppBundle\Entity\Organizer;
use AppBundle\Entity\User;

/**
 *
 */
class OrganizerFactory extends EntityFactory {
  /**
   * @param array $data
   * @return \AppBundle\Entity\Organizer|object
   */
  public function get(array $data) {
    $entity = $this->findExisting($data);
    if (!$entity) {
      $entity = $this->create($data);
    }

    return $entity;
  }

  private function create(array $data) {
    $entity = new Organizer();
    $user = $this->getUser();
    $entity->setCreatedBy($user);
    $this->setValues($entity, $data);
    $this->persist($entity);
    $this->flush();

    return $entity;
  }

  /**
   * @param array $data
   * @return \AppBundle\Entity\Organizer|object
   */
  private function findExisting(array $data) {
    // Try to find existing organizer by
    //   1. email
    //   2. url
    //   3. name
    $keys = ['email', 'url', 'name'];
    $repository = $this->em->getRepository(Organizer::class);

    foreach ($keys as $key) {
      if (isset($data[$key])) {
        $organizer = $repository->findOneBy([$key => $data[$key]]);
        if ($organizer) {
          return $organizer;
        }
      }
    }

    return NULL;
  }
}
