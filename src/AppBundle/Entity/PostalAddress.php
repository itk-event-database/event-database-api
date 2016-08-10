<?php


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * The mailing address.
 *
 * @see http://schema.org/PostalAddress Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(
 *   iri = "http://schema.org/PostalAddress",
 *   attributes = {
 *     "jsonld_embed_context" = true,
 *     "normalization_context" = { "groups" = { "event_read" } },
 *     "denormalization_context" = { "groups" = { "event_write" } },
  *   }
 * )
 */
class PostalAddress
{
  /**
   * @var integer
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;
  /**
   * @var string The country. For example, USA. You can also provide the two-letter [ISO 3166-1 alpha-2 country code](http://en.wikipedia.org/wiki/ISO_3166-1).
   * @Assert\Type(type="string")
   * @ORM\Column(nullable=true)
   */
  private $addressCountry;
  /**
   * @var string The locality. For example, Mountain View.
   * @Assert\Type(type="string")
   * @ORM\Column(nullable=true)
   */
  private $addressLocality;
  /**
   * @var string The region. For example, CA.
   * @Assert\Type(type="string")
   * @ORM\Column(nullable=true)
   */
  private $addressRegion;
  /**
   * @var string The postal code. For example, 94043.
   * @Assert\Type(type="string")
   * @ORM\Column(nullable=true)
   */
  private $postalCode;
  /**
   * @var string $postOfficeBoxNumber The post office box number for PO box addresses.
   * @Assert\Type(type="string")
   * @ORM\Column(nullable=true)
   */
  private $postOfficeBoxNumber;
  /**
   * @var string The street address. For example, 1600 Amphitheatre Pkwy.
   * @Assert\Type(type="string")
   * @ORM\Column(nullable=true)
   */
  private $streetAddress;

  /**
   * Sets id.
   *
   * @param  integer $id
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
   * @return integer
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Sets addressCountry.
   *
   * @param  string $addressCountry
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
   * @param  string $addressLocality
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
   * @param  string $addressRegion
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
   * @param  string $postalCode
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
   * Sets postOfficeBoxNumber.
   *
   * @param  string $postOfficeBoxNumber
   * @return $this
   */
  public function setPostOfficeBoxNumber($postOfficeBoxNumber)
  {
    $this->postOfficeBoxNumber = $postOfficeBoxNumber;

    return $this;
  }

  /**
   * Gets postOfficeBoxNumber.
   *
   * @return string
   */
  public function getPostOfficeBoxNumber()
  {
    return $this->postOfficeBoxNumber;
  }

  /**
   * Sets streetAddress.
   *
   * @param  string $streetAddress
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
}