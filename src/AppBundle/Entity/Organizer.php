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
 * Organizer of events.
 *
 * @ORM\Entity
 * @ORM\Table(
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="name_soft_unique",columns={"name", "deleted_at"}),
 *          @ORM\UniqueConstraint(name="email_soft_unique",columns={"email", "deleted_at"}),
 *          @ORM\UniqueConstraint(name="url_soft_unique",columns={"url", "deleted_at"})
 *      }
 * )
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ApiResource(
 *   collectionOperations={
 *     "get"={"method"="GET"}
 *   },
 *   itemOperations={
 *     "get"={"method"="GET"}
 *   },
 *   attributes = {
 *     "jsonld_embed_context" = true,
 *     "normalization_context" = { "groups" = { "organizer_read" } },
 *     "denormalization_context" = { "groups" = { "event_write", "organizer_write" } },
 *     "filters" = { "organizer.search", "organizer.order" }
 *   }
 * )
 */
class Organizer extends Entity implements Blameable
{
    use TimestampableEntity;
    use BlameableEntity;
    use SoftDeleteableEntity;

    /**
     * @var int
     *
     * @Groups({"organizer_read"})
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string the name
     *
     * @Groups({"organizer_read", "event_read", "event_write"})
     * @Assert\Type(type="string")
     * @ORM\Column()
     */
    private $name;

    /**
     * @var string the email address
     *
     * @Groups({"organizer_read", "event_read", "event_write"})
     * @Assert\Email(
     *   message = "The email '{{ value }}' is not a valid email."
     * )
     * @ORM\Column(nullable=true)
     */
    private $email;

    /**
     * @var string the url address
     *
     * @Groups({"organizer_read", "event_read", "event_write"})
     * @ORM\Column(nullable=true)
     */
    private $url;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="organizer")
     */
    private $events;

    /**
     * @var ArrayCollection
     *
     * @ORM\manyToMany(targetEntity="Event", mappedBy="partnerOrganizers")
     */
    private $partnerEvents;

    public function __construct()
    {
        $this->partnerEvents = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
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

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function getPartnerEvents()
    {
        return $this->partnerEvents;
    }

    public function addPartnerEvent($partnerEvent)
    {
        if (!$this->partnerEvents->contains($partnerEvent)) {
            $this->partnerEvents->add($partnerEvent);
        }

        return $this;
    }
}
