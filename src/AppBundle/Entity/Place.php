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
 *     "normalization_context" = { "groups" = { "place_read" } },
 *     "denormalization_context" = { "groups" = { "place_write" } },
 *   }
 * )
 */
class Place extends Thing
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
   * @var Feed
   *
   * @ORM\ManyToOne(targetEntity="AdminBundle\Entity\Feed")
   */
  private $feed;

  /**
   * @var PostalAddress Physical address of the item.
   * @ORM\ManyToOne(targetEntity="PostalAddress")
   */
  private $address;

  /**
   * @var string The telephone number.
   * @Assert\Type(type="string")
   * @ORM\Column(nullable=true)
   */
  private $telephone;

  /**
   * @var string The name of the item.
   *
   * @Groups({"event_read", "event_write"})
   * @ORM\Column(nullable=true)
   * @Assert\Type(type="string")
   * @ApiProperty(iri="http://schema.org/image")
   */
  private $logo;

  /**
   * @var ArrayCollection
   * @ORM\OneToMany(targetEntity="Occurrence", mappedBy="place")
   * @Groups({"place_read"})
   */
  private $occurrences;

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

  public function setFeed($feed) {
    $this->feed = $feed;

    return $this;
  }

  public function getFeed() {
    return $this->feed;
  }

  /**
   * Sets address.
   *
   * @param  PostalAddress $address
   * @return $this
   */
  public function setAddress(PostalAddress $address)
  {
    $this->address = $address;

    return $this;
  }

  /**
   * Gets address.
   *
   * @return PostalAddress
   */
  public function getAddress()
  {
    return $this->address;
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
}
