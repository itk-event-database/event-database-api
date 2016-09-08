<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use FPN\TagBundle\Entity\TagManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * An occurrence of an Event.
 *
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ApiResource(
 *   attributes = {
 *     "jsonld_embed_context" = true,
 *     "normalization_context" = { "groups" = { "event_read", "place_read" } },
 *     "denormalization_context" = { "groups" = { "event_write" } },
 *     "filters" = { "occurrence.search.date", "occurrence.order" }
 *   }
 * )
 */
class Occurrence extends Entity {
  use SoftdeleteableEntity;

  /**
   * @var int
   *
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ORM\ManyToOne(targetEntity="Event", inversedBy="occurrences")
   * @Groups({"event_read", "event_write"})
   */
  protected $event;

  /**
   * @var \DateTime
   * @ORM\Column(type="datetime", nullable=true)
   * @Groups({"event_read", "event_write"})
   */
  protected $startDate;

  /**
   * @var \DateTime
   * @ORM\Column(type="datetime", nullable=true)
   * @Groups({"event_read", "event_write"})
   */
  protected $endDate;

  /**
   * @var Place
   * @ORM\ManyToOne(targetEntity="Place", inversedBy="occurrences")
   * @Groups({"event_read", "event_write"})
   */
  protected $place;

  /**
   * Sets id.
   *
   * @param int $id
   *
   * @return $this
   */
  public function setId($id) {
    $this->id = $id;

    return $this;
  }

  /**
   * Gets id.
   *
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  public function setEvent(Event $event = null) {
    $this->event = $event;

    return $this;
  }

  public function getEvent() {
    return $this->event;
  }

  /**
   * Sets startDate.
   *
   * @param \DateTime $startDate
   *
   * @return $this
   */
  public function setStartDate(\DateTime $startDate = null) {
    $this->startDate = $startDate;

    return $this;
  }

  /**
   * Gets startDate.
   *
   * @return \DateTime
   */
  public function getStartDate() {
    return $this->startDate;
  }

  /**
   * Sets endDate.
   *
   * @param \DateTime $endDate
   *
   * @return $this
   */
  public function setEndDate(\DateTime $endDate = null) {
    $this->endDate = $endDate;

    return $this;
  }

  /**
   * Gets endDate.
   *
   * @return \DateTime
   */
  public function getEndDate() {
    return $this->endDate;
  }

  public function setPlace(Place $place) {
    $this->place = $place;

    return $this;
  }

  public function getPlace() {
    return $this->place;
  }

  /**
   * Sets venue.
   *
   * @param string $venue
   *
   * @return $this
   */
  public function setVenue($venue) {
    $this->venue = $venue;

    return $this;
  }

  /**
   * Gets venue.
   *
   * @return string
   */
  public function getVenue() {
    return $this->venue;

  }
}
