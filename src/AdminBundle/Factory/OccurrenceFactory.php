<?php

namespace AdminBundle\Factory;

use AppBundle\Entity\Entity;
use AppBundle\Entity\Occurrence;

/**
 *
 */
class OccurrenceFactory extends EntityFactory
{
  /**
   * @var PlaceFactory
   */
    protected $placeFactory;

  /**
   * @param \AdminBundle\Factory\PlaceFactory $placeFactory
   */
    public function setPlaceFactory(PlaceFactory $placeFactory)
    {
        $this->placeFactory = $placeFactory;
    }

  /**
   * @param array $data
   * @return \AppBundle\Entity\Occurrence
   */
    public function get(array $data)
    {
        $entity = $this->getEntity($data);
        $this->setValues($entity, $data);

        return $entity;
    }

  /**
   * @param array $data
   * @return \AppBundle\Entity\Occurrence
   */
    private function getEntity(array $data)
    {
        $occurrence = new Occurrence();

        return $occurrence;
    }

  /**
   * @param \AppBundle\Entity\Entity $entity
   * @param $key
   * @param $value
   */
    protected function setValue(Entity $entity, $key, $value)
    {
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
