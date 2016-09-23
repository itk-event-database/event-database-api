<?php

namespace AppBundle\Entity;

use AdminBundle\Entity\Feed;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use DoctrineExtensions\Taggable\Doctrine;
use DoctrineExtensions\Taggable\Taggable;
use FPN\TagBundle\Entity\TagManager;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use AppBundle\Traits\BlameableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\PropertyAccess\PropertyAccessor;
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
 *     "filters" = { "event.search", "event.search.date", "event.search.tag", "event.order", "event.order.default" }
 *   }
 * )
 */
class Event extends Thing implements Taggable
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
   * @ORM\OneToMany(targetEntity="Occurrence", mappedBy="event", cascade={"persist", "remove"}, orphanRemoval=true)
   * @ORM\OrderBy({"startDate"="ASC", "endDate"="ASC"})
   */
  private $occurrences;

  /**
   * @var string The URI for ticket purchase
   *
   * @Groups({"event_read", "event_write"})
   * @ORM\Column(nullable=true)
   * @Assert\Type(type="string")
   * @ApiProperty(iri="http://schema.org/url")
   */
  private $ticketPurchaseUrl;

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

  /**
   * @return mixed
   */
  public function getTicketPurchaseUrl()
  {
    return $this->ticketPurchaseUrl;
  }

  /**
   * @param mixed $ticketPurchaseUrl
   */
  public function setTicketPurchaseUrl($ticketPurchaseUrl)
  {
    $this->ticketPurchaseUrl = $ticketPurchaseUrl;
  }

  public function __construct() {
    $this->occurrences = new ArrayCollection();
  }

  /**
   * @var ArrayCollection
   *
   * @Groups({"event_read", "event_write"})
   * @ ORM\Column(type="array", nullable=true)
   */
  private $tags;

  /**
   * Returns the unique taggable resource type
   *
   * @return string
   */
  function getTaggableType()
  {
    return 'event';
  }

  /**
   * Returns the unique taggable resource identifier
   *
   * @return string
   */
  function getTaggableId()
  {
    return $this->getId();
  }

  // Method stub needed to make CustomItemNormalizer work. If no setter is
  // defined, tags will not be processed during normalization.
  function setTags($tags) {}

  /**
   * Returns the collection of tags for this Taggable entity
   *
   * @return Doctrine\Common\Collections\Collection
   */
  function getTags()
  {
    $this->tags = $this->tags ?: new ArrayCollection();
    return $this->tags;
  }
}
