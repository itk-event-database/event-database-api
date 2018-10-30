<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\Controller\OccurrencesCalendar;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An occurrence of an Event.
 *
 * @ORM\Entity
 *
 * @ApiResource(
 *   attributes = {
 *     "jsonld_embed_context" = true,
 *     "normalization_context" = { "groups" = { "occurrence_read" } },
 *     "denormalization_context" = { "groups" = { "event_write" } },
 *     "filters" = { "occurrence.search", "occurrence.search.date", "occurrence.search.event_tag", "occurrence.search.published", "occurrence.order" }
 *   },
 *   collectionOperations={
 *     "get",
 *     "calendar"={
 *         "method"="GET",
 *         "path"="/occurrences/calendar.{_format}",
 *         "controller"=OccurrencesCalendar::class
 *     }
 *   }
 * )
 * @ORM\Table(
 *   indexes={
 *     @ORM\Index(name="IDX_OCCURRENCE_DATES", columns={"start_date", "end_date"})
 *   }
 * )
 */
class Occurrence extends Entity
{
    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="occurrences", fetch="EAGER")
     * @Groups({"occurrence_read", "event_read", "event_write"})
     */
    protected $event;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"occurrence_read", "event_read", "event_write"})
     */
    protected $startDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"occurrence_read", "event_read", "event_write"})
     */
    protected $endDate;

    /**
     * @var Place
     * @ORM\ManyToOne(targetEntity="Place", inversedBy="occurrences", fetch="EAGER")
     * @Groups({"occurrence_read", "event_read", "event_write"})
     */
    protected $place;
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string the room the event is held in
     *
     * @Groups({"occurrence_read", "event_read", "event_write"})
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     */
    private $room;

    /**
     * @var string the range of prices for tickets
     *
     * @Groups({"occurrence_read", "event_read", "event_write"})
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     */
    private $ticketPriceRange;

    /**
     * @var string The status of the event
     *
     * @Groups({"occurrence_read", "event_read", "event_write"})
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     */
    private $eventStatusText;

    /**
     * @var int The status code of the event
     *
     * 0: Normal
     * 1: Few tickets left
     * 2: Sold out
     * 3: Cancelled
     * 4: Not in sale
     * 5: Waiting
     * 6: Moved
     * 7: Ekstra show
     *
     * @Groups({"occurrence_read", "event_read", "event_write"})
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="integer")
     */
    private $eventSalesStatus;

    public function __toString()
    {
        $start = empty($this->getStartDate()) ? '?' : $this->getStartDate()->format('Y-m-d H:i');
        $end = empty($this->getEndDate()) ? '?' : $this->getEndDate()->format('Y-m-d H:i');

        return $start.' - '.$end.($this->getPlace() ? ' @ '.$this->getPlace()->getName() : '');
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

    public function setEvent(Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    public function getEvent()
    {
        return $this->event;
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

    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Sets room.
     *
     * @param string $room
     *
     * @return $this
     */
    public function setRoom($room)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Gets room.
     *
     * @return string
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @return string
     */
    public function getTicketPriceRange()
    {
        return $this->ticketPriceRange;
    }

    /**
     * @param string $ticketPriceRange
     */
    public function setTicketPriceRange($ticketPriceRange)
    {
        $this->ticketPriceRange = $ticketPriceRange;
    }

    /**
     * @return string
     */
    public function getEventStatusText()
    {
        return $this->eventStatusText;
    }

    /**
     * @param string $eventStatusText
     */
    public function setEventStatusText($eventStatusText)
    {
        $this->eventStatusText = $eventStatusText;
    }
}
