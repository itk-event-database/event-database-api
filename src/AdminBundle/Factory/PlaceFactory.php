<?php
/**
 * Created by PhpStorm.
 * User: turegjorup
 * Date: 11/08/16
 * Time: 13:32
 */

namespace AdminBundle\Factory;


use AppBundle\Entity\Place;

class PlaceFactory extends EntityFactory
{

  public function get(Feed $feed, $name)
  {
    $query = $this->em->createQuery('SELECT p FROM AppBundle:Place p WHERE p.feed_id = :feedId AND WHERE p.name = :name');
    $query->setParameter('feedId', $feed->id);
    $query->setParameter('name', $name);

    $places = $query->getResult();

    if (count($places) === 0) {
      $place = new Place();
      $place->setFeedEventId($feed->id);
      return $place;
    }
    return $places[0];

  }
}