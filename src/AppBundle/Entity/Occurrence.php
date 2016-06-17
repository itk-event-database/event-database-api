<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * An occurrence of an Event.
 *
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Occurrence {
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
   * @ORM\Column(type="date", nullable=true)
   * @Groups({"event_read", "event_write"})
   */
  protected $startTime;

  /**
   * @var \DateTime
   * @ORM\Column(type="date", nullable=true)
   * @Groups({"event_read", "event_write"})
   */
  protected $endTime;

  /**
   * @var string
   * @ORM\Column(type="string", length=255, nullable=true)
   * @Groups({"event_read", "event_write"})
   */
  protected $venue;

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
   * Sets startTime.
   *
   * @param \DateTime $startTime
   *
   * @return $this
   */
  public function setStartTime(\DateTime $startTime = null) {
    $this->startTime = $startTime;

    return $this;
  }

  /**
   * Gets startTime.
   *
   * @return \DateTime
   */
  public function getStartTime() {
    return $this->startTime;
  }

  /**
   * Sets endTime.
   *
   * @param \DateTime $endTime
   *
   * @return $this
   */
  public function setEndTime(\DateTime $endTime = null) {
    $this->endTime = $endTime;

    return $this;
  }

  /**
   * Gets endTime.
   *
   * @return \DateTime
   */
  public function getEndTime() {
    return $this->endTime;
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

  public function setValues(array $values) {
    foreach ($values as $key => $value) {
      switch ($key) {
        case 'event':
          break;
        default:
          $methodName = 'set' . $key;
          if (method_exists($this, $methodName)) {
            $this->{$methodName}($value);
          }
          break;
      }
    }
  }
}
