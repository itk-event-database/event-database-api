<?php

namespace AdminBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use AppBundle\Traits\BlameableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Feed
 *
 * @ORM\Table()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ORM\Entity
 */
class Feed
{
  use TimestampableEntity;
  use BlameableEntity;
  use SoftdeleteableEntity;

  /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="json_array")
     */
    private $configuration;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastRead", type="datetime", nullable=true)
     */
    private $lastRead;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Feed
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set configuration
     *
     * @param array $configuration
     * @return Feed
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Get configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Set lastRead
     *
     * @param \DateTime $lastRead
     * @return Feed
     */
    public function setLastRead($lastRead)
    {
        $this->lastRead = $lastRead;

        return $this;
    }

    /**
     * Get lastRead
     *
     * @return \DateTime
     */
    public function getLastRead()
    {
        return $this->lastRead;
    }

  public function getUrl() {
    return isset($this->configuration['url']) ? $this->configuration['url'] : null;
  }

  public function getType() {
    return isset($this->configuration['type']) ? $this->configuration['type'] : null;
  }

  public function getRoot() {
    return isset($this->configuration['root']) ? $this->configuration['root'] : null;
  }

  public function getMapping() {
    return isset($this->configuration['mapping']) ? $this->configuration['mapping'] : null;
  }

  public function getBaseUrl() {
    return isset($this->configuration['baseUrl']) ? $this->configuration['baseUrl'] : null;
  }

  public function getDefaults() {
    return isset($this->configuration['defaults']) ? $this->configuration['defaults'] : null;
  }
}
