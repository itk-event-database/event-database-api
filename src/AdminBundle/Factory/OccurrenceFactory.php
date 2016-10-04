<?php

namespace AdminBundle\Factory;

use AppBundle\Entity\Entity;
use AppBundle\Entity\Occurrence;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class OccurrenceFactory extends EntityFactory
{
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

  protected function setValue(Entity $entity, $key, $value, PropertyAccessor $accessor) {
    if ($accessor->isWritable($entity, $key)) {
      if ($key == 'place') {
        $value = $this->placeFactory->get($value);
      }
    }
    parent::setValue($entity, $key, $value, $accessor);
  }

}