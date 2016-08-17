<?php

namespace AdminBundle\Factory;

use AppBundle\Entity\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use FPN\TagBundle\Entity\TagManager;

use AppBundle\Entity\Occurrence;
use AdminBundle\Entity\Feed;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class OccurrenceFactory extends EntityFactory
{
  protected $placeFactory;

  public function setPlaceFactory(PlaceFactory $placeFactory) {
    $this->placeFactory = $placeFactory;
  }

  public function setFeed(Feed $feed) {
    parent::setFeed($feed);
    $this->placeFactory->setFeed($feed);
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
        if (is_array($value) && count($value) > 0) {
          $value = $this->placeFactory->get($value[0]);
        } else {
          $value = null;
        }
      }
    }
    parent::setValue($entity, $key, $value, $accessor);
  }

}