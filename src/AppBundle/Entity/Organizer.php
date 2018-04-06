<?php

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
   * @var string The name.
   *
   * @Groups({"organizer_read", "event_read", "event_write"})
   * @Assert\Type(type="string")
   * @ORM\Column()
   */
    private $name;

  /**
   * @var string The email address.
   *
   * @Groups({"organizer_read", "event_read", "event_write"})
   * @Assert\Email(
   *   message = "The email '{{ value }}' is not a valid email."
   * )
   * @ORM\Column()
   */
    private $email;

  /**
   * @var string The url address.
   *
   * @Groups({"organizer_read", "event_read", "event_write"})
   * @ORM\Column()
   */
    private $url;

  /**
   * @var ArrayCollection
   *
   * @ORM\OneToMany(targetEntity="Event", mappedBy="organizer")
   * @Groups({"organizer_read"})
   */
    private $events;

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

    public function __toString()
    {
        return $this->getName();
    }
}
