<?php

namespace AppBundle\Entity;

use AdminBundle\Entity\Feed;

use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use AppBundle\Traits\BlameableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Doctrine\ORM\Mapping as ORM;
use Dunglas\ApiBundle\Annotation\Iri;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * An event happening at a certain time and location, such as a concert, lecture, or festival. Ticketing information may be added via the 'offers' property. Repeated events may be structured as separate Event objects.
 *
 * @see http://schema.org/Event Documentation on Schema.org
 *
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Iri("http://schema.org/Event")
 */
class Event extends Thing
{
  use TimestampableEntity;
  use BlameableEntity;
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
   * @var ArrayCollection
   *
   * @Groups({"event_read", "event_write"})
   * @ORM\OneToMany(targetEntity="Occurrence", mappedBy="event", cascade={"persist", "remove"})
   */
  private $occurrences;

  /**
   * @var Feed
   *
   * @ORM\ManyToOne(targetEntity="AdminBundle\Entity\Feed")
   */
  private $feed;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  private $feedEventId;

  /**
   * Sets id.
   *
   * @param int $id
   *
   * @return $this
   */
  public function setId($id)
  {
    $this->id = $id;

    return $this;
  }

  /**
   * Gets id.
   *
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Sets endDate.
   *
   * @param \DateTime $endDate
   *
   * @return $this
   */
  public function setEndDate(\DateTime $endDate = null)
  {
    $this->endDate = $endDate;

    return $this;
  }

  /**
   * Gets endDate.
   *
   * @return \DateTime
   */
  public function getEndDate()
  {
    return $this->endDate;
  }

  /**
   * Sets startDate.
   *
   * @param \DateTime $startDate
   *
   * @return $this
   */
  public function setStartDate(\DateTime $startDate = null)
  {
    $this->startDate = $startDate;

    return $this;
  }

  /**
   * Gets startDate.
   *
   * @return \DateTime
   */
  public function getStartDate()
  {
    return $this->startDate;
  }

  public function setOccurrences($occurrences) {
    // Orphan any existing occurrences.
    if ($this->occurrences) {
      $now = new \DateTime();
      foreach ($this->occurrences as $occurrence) {
        $occurrence->setDeletedAt($now);
      }
    }

    $this->occurrences = $occurrences;

    foreach ($this->occurrences as $occurrence) {
      $occurrence->setEvent($this);
    }

    return $this;
  }

  /**
   * @return Collection
   */
  public function getOccurrences() {
    return $this->occurrences;
  }

  public function setFeed($feed) {
    $this->feed = $feed;

    return $this;
  }

  public function getFeed() {
    return $this->feed;
  }

  public function setFeedEventId($feedEventId) {
    $this->feedEventId = $feedEventId;

    return $this;
  }

  public function getFeedEventId() {
    return $this->feedEventId;
  }

  public function __construct() {
    $this->occurrences = new ArrayCollection();
  }

  /**
   * Set values from an array.
   */
  public function setValues(array $values) {
    foreach ($values as $key => $value) {
      switch ($key) {
        case 'occurrences':
          $occurrences = new ArrayCollection();
          foreach ($value as $item) {
            $occurrence = new Occurrence();
            $occurrence->setValues($item);
            $occurrences->add($occurrence);
          }
          $this->setOccurrences($occurrences);
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
