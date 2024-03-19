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
use DoctrineExtensions\Taggable\Taggable;
use Gedmo\Blameable\Blameable;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entities that have a somewhat fixed, physical extension.
 *
 * @see http://schema.org/Place Documentation on Schema.org
 *
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ApiResource(
 *   iri = "http://schema.org/Place",
 *   attributes = {
 *     "jsonld_embed_context" = true,
 *     "normalization_context" = { "groups" = { "place_read", "event_read", "occurrence_read" } },
 *     "denormalization_context" = { "groups" = { "event_write", "place_write" } },
 *     "filters" = { "place.search", "place.search.tag", "place.search.geolocation", "place.search.owner", "place.order" }
 *   }
 * )
 */
class Place extends Thing implements Taggable, Blameable
{
    use TimestampableEntity;
    use BlameableEntity;
    use SoftDeleteableEntity;

    /**
     * @var int
     *
     * @Groups({"place_read"})
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Feed
     *
     * @ORM\ManyToOne(targetEntity="AdminBundle\Entity\Feed")
     */
    private $feed;

    /**
     * @var string the telephone number
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @Assert\Type(type="string")
     * @ORM\Column(nullable=true)
     */
    private $telephone;

    /**
     * @var string the email address
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @Assert\Email(
     *   message = "The email '{{ value }}' is not a valid email."
     * )
     * @ORM\Column(nullable=true)
     */
    private $email;

    /**
     * @var string the logo of the item
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/image")
     */
    private $logo;

    /**
     * @var bool Does the place have disability access?
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $disabilityAccess;

    /**
     * @var string The country. For example, USA. You can also provide the two-letter [ISO 3166-1 alpha-2 country code](http://en.wikipedia.org/wiki/ISO_3166-1).
     *
     * @Groups({"event_read", "place_read", "occurrence_read", "place_write"})
     * @Assert\Type(type="string")
     * @ORM\Column(nullable=true)
     */
    private $addressCountry;

    /**
     * @var string The locality. For example, Mountain View.
     *
     * @Groups({"event_read", "place_read", "occurrence_read", "place_write"})
     * @Assert\Type(type="string")
     * @ORM\Column(nullable=true)
     */
    private $addressLocality;

    /**
     * @var string The region. For example, CA.
     *
     * @Groups({"event_read", "place_read", "occurrence_read", "place_write"})
     * @Assert\Type(type="string")
     * @ORM\Column(nullable=true)
     */
    private $addressRegion;

    /**
     * @var string The postal code. For example, 94043.
     *
     * @Groups({"event_read", "place_read", "occurrence_read", "place_write"})
     * @Assert\Type(type="string")
     * @ORM\Column(nullable=true)
     */
    private $postalCode;

    /**
     * @var string The street address. For example, 1600 Amphitheatre Pkwy.
     *
     * @Groups({"event_read", "place_read", "occurrence_read", "place_write"})
     * @Assert\Type(type="string")
     * @ORM\Column(nullable=true)
     */
    private $streetAddress;

    /**
     * @var number The latitude of the location
     *
     * @Groups({"event_read", "place_read", "occurrence_read", "place_write"})
     * @ORM\Column(nullable=true, type="float")
     */
    private $latitude;

    /**
     * @var number The longitude of the location
     *
     * @Groups({"event_read", "place_read", "occurrence_read", "place_write"})
     * @ORM\Column(nullable=true, type="float")
     */
    private $longitude;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Occurrence", mappedBy="place")
     */
    private $occurrences;

    /**
     * @var ArrayCollection
     *
     * @Groups({"event_read", "event_write"})
     * @ ORM\Column(type="array", nullable=true)
     */
    private $tags;

    public function __toString()
    {
        $values = [
        $this->getName(),
        $this->getStreetAddress(),
        $this->getPostalCode().' '.$this->getAddressLocality(),
        $this->getAddressCountry(),
        ];

        return implode(', ', array_filter(array_map('trim', $values)));
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

    public function setFeed($feed)
    {
        $this->feed = $feed;

        return $this;
    }

    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * Sets addressCountry.
     *
     * @param string $addressCountry
     *
     * @return $this
     */
    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    /**
     * Gets addressCountry.
     *
     * @return string
     */
    public function getAddressCountry()
    {
        return $this->addressCountry;
    }

    /**
     * Sets addressLocality.
     *
     * @param string $addressLocality
     *
     * @return $this
     */
    public function setAddressLocality($addressLocality)
    {
        $this->addressLocality = $addressLocality;

        return $this;
    }

    /**
     * Gets addressLocality.
     *
     * @return string
     */
    public function getAddressLocality()
    {
        return $this->addressLocality;
    }

    /**
     * Sets addressRegion.
     *
     * @param string $addressRegion
     *
     * @return $this
     */
    public function setAddressRegion($addressRegion)
    {
        $this->addressRegion = $addressRegion;

        return $this;
    }

    /**
     * Gets addressRegion.
     *
     * @return string
     */
    public function getAddressRegion()
    {
        return $this->addressRegion;
    }

    /**
     * Sets postalCode.
     *
     * @param string $postalCode
     *
     * @return $this
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Gets postalCode.
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Sets streetAddress.
     *
     * @param string $streetAddress
     *
     * @return $this
     */
    public function setStreetAddress($streetAddress)
    {
        $this->streetAddress = $streetAddress;

        return $this;
    }

    /**
     * Gets streetAddress.
     *
     * @return string
     */
    public function getStreetAddress()
    {
        return $this->streetAddress;
    }

    /**
     * Sets logo.
     *
     * @param string $logo
     *
     * @return $this
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Gets logo.
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Sets occurrences.
     *
     * @param ArrayCollection $occurrences
     *
     * @return $this
     */
    public function setOccurrences(ArrayCollection $occurrences)
    {
        $this->occurrences = $occurrences;

        return $this;
    }

    /**
     * Gets occurrences.
     *
     * @return ArrayCollection
     */
    public function getOccurrences()
    {
        return $this->occurrences;
    }

    /**
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @param string $telephone
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    /**
     * @return bool
     */
    public function isDisabilityAccess()
    {
        return $this->disabilityAccess;
    }

    /**
     * @param bool $disabilityAccess
     */
    public function setDisabilityAccess($disabilityAccess)
    {
        $this->disabilityAccess = $disabilityAccess;
    }

    /**
     * @return number
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param number $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return number
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param number $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
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
     * Returns the unique taggable resource type.
     *
     * @return string
     */
    public function getTaggableType()
    {
        return 'place';
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

    // Method stub needed to make CustomItemNormalizer work. If no setter is
    // defined, tags will not be processed during normalization.

    public function setTags($tags)
    {
    }

    /**
     * Returns the collection of tags for this Taggable entity.
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();

        return $this->tags;
    }
}
