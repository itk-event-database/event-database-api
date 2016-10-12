<?php

namespace AdminBundle\Factory;

use AppBundle\Entity\Entity;
use AppBundle\Entity\Occurrence;

class OccurrenceFactory extends EntityFactory
{
  /**
   * @var PlaceFactory
   */
  protected $placeFactory;

  public function setPlaceFactory(PlaceFactory $placeFactory) {
    $this->placeFactory = $placeFactory;
  }

  public function get(array $data) {
    $entity = $this->getEntity($data);
    $this->setValues($entity, $data);

    return $entity;
  }

  private function getEntity(array $data) {
    $occurrence = new Occurrence();

    return $occurrence;
  }

  protected function setValue(Entity $entity, $key, $value) {
    if ($entity instanceof Occurrence) {
      if ($this->accessor->isWritable($entity, $key)) {
        if ($key == 'place') {
          $value = $this->placeFactory->get($value);
        }
      }
    }

    parent::setValue($entity, $key, $value);
  }

}