<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Entity;

use AdminBundle\Entity\Feed;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\Traits\BlameableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Blameable;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An event happening at a certain time and location, such as a concert, lecture, or festival. Ticketing information may be added via the 'offers' property. Repeated events may be structured as separate Event objects.
 *
 * @see http://schema.org/Event Documentation on Schema.org
 *
 * @ORM\Entity
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @ApiResource(
 *   iri = "http://schema.org/Event",
 *   attributes = {
 *     "jsonld_embed_context" = true,
 *     "normalization_context" = { "groups" = { "event_read" } },
 *     "denormalization_context" = { "groups" = { "event_write" } },
 *     "filters" = { "event.search", "event.search.date", "event.search.tag", "event.search.owner", "event.search.published", "event.search.access", "event.order", "event.order.default" },
 *     "validation_groups"={"event_write"}
 *   }
 * )
 * @ORM\Table(
 *   indexes={
 *     @ORM\Index(name="IDX_EVENT_NAME", columns={"name"})
 *   }
 * )
 */
class Event extends Thing implements CustomTaggable, Blameable
{
    use TimestampableEntity;
    use BlameableEntity;
    use SoftDeleteableEntity;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     * @Groups({"event_read"})
     */
    protected $updatedAt;

    /**
     * The primary event organizer.
     *
     * @var Organizer
     * @ORM\ManyToOne(targetEntity="Organizer", inversedBy="events")
     * @Groups({"event_read", "event_write", "occurrence_read"})
     */
    protected $organizer;

    /**
     * Partner Organizers.
     *
     * @var ArrayCollection
     *
     * @Groups({"event_read", "event_write", "occurrence_read"})
     * @ORM\ManyToMany(targetEntity="Organizer", inversedBy="partnerEvents", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinTable(name="event__partner_organizers",
     *      joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="organizer_id", referencedColumnName="id")}
     * )
     */
    private $partnerOrganizers;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool
     *
     * @Groups({"event_read", "event_write"})
     * @ORM\Column(type="boolean")
     * @Assert\Type(type="boolean")
     * @ApiProperty(iri="http://schema.org/Boolean")
     */
    private $isPublished = true;

    /**
     * @var bool
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @ORM\Column(type="boolean")
     * @Assert\Type(type="boolean")
     * @ApiProperty(iri="http://schema.org/Boolean")
     */
    private $hasFullAccess = true;

    /**
     * @var ArrayCollection
     *
     * @Groups({"event_read", "event_write"})
     * @ORM\OneToMany(targetEntity="Occurrence", mappedBy="event", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"startDate"="ASC", "endDate"="ASC"})
     * @Assert\Count(min=1, minMessage="You must specify at least one occurrence", groups={"event_write"})
     */
    private $occurrences;

    /**
     * @var string The URI for ticket purchase
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/url")
     */
    private $ticketPurchaseUrl;

    /**
     * @var string the URI for (Facebook) event
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/url")
     */
    private $eventUrl;

    /**
     * @var string Excerpt, i.e. short description, without any markup
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "The excerpt cannot be longer than {{ limit }} characters"
     * )
     */
    private $excerpt;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event")
     */
    private $master;

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
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $feedEventHash;

    /**
     * @var ArrayCollection
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     */
    private $tags;

    /**
     * @var ArrayCollection
     *
     * @Groups({"event_read"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $customTags;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $repeatingOccurrences = [];

    public function __construct()
    {
        $this->occurrences = new ArrayCollection();
        $this->partnerOrganizers = new ArrayCollection();
    }

    public function __clone()
    {
        $this->setId(null);
        $this->setIsPublished(false);
        $self = $this;
        $this->occurrences = $this->getOccurrences()->map(function ($occurrence) use ($self) {
            $clone = clone $occurrence;
            $clone->setId(null);
            $clone->setEvent($self);

            return $clone;
        });
        $this->partnerOrganizers = $this->getPartnerOrganizers()->map(function ($organizer) use ($self) {
            $organizer->addAdditionalEvent($self);

            return $organizer;
        });
    }

    public function __toString()
    {
        return $this->getName();
    }

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
     * Sets isPublished.
     *
     * @param int $isPublished
     *
     * @return $this
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * Gets isPublished.
     *
     * @return int
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Sets hasFullAccess.
     *
     * @param bool $hasFullAccess
     *
     * @return $this
     */
    public function setHasFullAccess(bool $hasFullAccess)
    {
        $this->hasFullAccess = $hasFullAccess;

        return $this;
    }

    /**
     * Gets hasFullAccess.
     *
     * @return bool
     */
    public function getHasFullAccess()
    {
        return $this->hasFullAccess;
    }

    public function setOccurrences($occurrences)
    {
        $this->occurrences->clear();

        foreach ($occurrences as $occurrence) {
            $this->occurrences->add($occurrence);
            $occurrence->setEvent($this);
        }

        return $this;
    }

    public function getOccurrences()
    {
        return $this->occurrences;
    }

    public function setFeed($feed)
    {
        $this->feed = $feed;

        return $this;
    }

    public function getFeed()
    {
        return $this->feed;
    }

    public function setFeedEventId($feedEventId)
    {
        $this->feedEventId = $feedEventId;

        return $this;
    }

    public function getFeedEventId()
    {
        return $this->feedEventId;
    }

    public function setFeedEventHash($feedEventHash)
    {
        $this->feedEventHash = $feedEventHash;

        return $this;
    }

    public function getFeedEventHash()
    {
        return $this->feedEventHash;
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

    /**
     * @return null|string
     */
    public function getEventUrl()
    {
        return $this->eventUrl;
    }

    /**
     * @param string $eventUrl
     */
    public function setEventUrl($eventUrl)
    {
        $this->eventUrl = $eventUrl;
    }

    /**
     * @return string
     */
    public function getExcerpt()
    {
        return $this->excerpt;
    }

    /**
     * @param string $excerpt
     */
    public function setExcerpt($excerpt)
    {
        $this->excerpt = $excerpt;
    }

    public function setOrganizer(Organizer $organizer = null)
    {
        $this->organizer = $organizer;
    }

    public function getOrganizer()
    {
        return $this->organizer;
    }

    public function addPartnerOrganizers($partnerOrganizer)
    {
        if (!$this->partnerOrganizers->contains($partnerOrganizer)) {
            $this->partnerOrganizers->add($partnerOrganizer);
            $partnerOrganizer->addAdditionalEvent($this);
        }

        return $this;
    }

    public function setPartnerOrganizers($partnerOrganizers)
    {
        $this->partnerOrganizers->clear();

        foreach ($partnerOrganizers as $partnerOrganizer) {
            $this->partnerOrganizers->add($partnerOrganizer);
            $partnerOrganizer->addAdditionalEvent($this);
        }

        return $this;
    }

    public function getPartnerOrganizers()
    {
        return $this->partnerOrganizers;
    }

    /**
     * Sets master.
     *
     * @param string $master
     *
     * @return $this
     */
    public function setMaster($master)
    {
        $this->master = $master;

        return $this;
    }

    /**
     * Gets master.
     *
     * @return string
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * Returns the unique taggable resource type.
     *
     * @return string
     */
    public function getTaggableType()
    {
        return 'event';
    }

    /**
     * Returns the unique taggable resource identifier.
     *
     * @return string
     */
    public function getTaggableId()
    {
        return $this->getId();
    }

    /**
     * Method stub needed to make CustomItemNormalizer work. If no setter is
     * defined, tags will not be processed during normalization.
     *
     * @param mixed $tags
     */
    public function setTags($tags)
    {
    }

    /**
     * Returns the collection of tags for this Taggable entity.
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();

        return $this->tags;
    }

    public function setCustomTags(array $customTags)
    {
        $this->customTags = $customTags;

        return $this;
    }

    /**
     * Returns the collection of tags for this Taggable entity.
     *
     * @return ArrayCollection
     */
    public function getCustomTags()
    {
        $this->customTags = $this->customTags ?: new ArrayCollection();

        return $this->customTags;
    }

    public function setRepeatingOccurrences(array $repeatingOccurrences)
    {
        $this->repeatingOccurrences = $repeatingOccurrences;
    }

    public function getRepeatingOccurrences()
    {
        return $this->repeatingOccurrences;
    }
}
