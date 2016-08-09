<?php

namespace AppBundle\Entity;

use AdminBundle\Entity\Feed;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use AppBundle\Traits\BlameableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Doctrine\ORM\Mapping as ORM;
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
 * @ApiResource(
 *   iri = "http://schema.org/Event",
 *   attributes = {
 *     "jsonld_embed_context" = true,
 *     "normalization_context" = { "groups" = { "event_read" } },
 *     "denormalization_context" = { "groups" = { "event_write" } },
 *     "filters" = { "event.search", "event.search.date", "event.order", "event.order.default" }
 *   }
 * )
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
   * @var Place The location of for example where the event is happening, an organization is located, or where an action takes place.
   * @ORM\ManyToOne(targetEntity="Place", inversedBy="events")
   */
  private $location;

  /**
   * @var ArrayCollection
   *
   * @Groups({"event_read", "event_write"})
   * @ORM\OneToMany(targetEntity="Occurrence", mappedBy="event", cascade={"persist", "remove"}, orphanRemoval=true)
   * @ORM\OrderBy({"startDate"="ASC", "endDate"="ASC"})
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
   * @return Place
   */
  public function getLocation()
  {
    return $this->location;
  }

  /**
   * @param Place $location
   */
  public function setLocation($location)
  {
    $this->location = $location;
  }

  public function setOccurrences($occurrences) {
    if ($this->occurrences) {
      $this->occurrences->clear();
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

}
