<?php

namespace AdminBundle\Entity;

use AppBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use AppBundle\Traits\BlameableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Feed.
 *
 * @ORM\Table()
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @ORM\Entity
 */
class Feed {
  use TimestampableEntity;
  use BlameableEntity;
  use SoftDeleteableEntity;

  const FEED_CLEAN_UP_NONE = 'FEED_CLEAN_UP_NONE';
  const FEED_CLEAN_UP_FUTURE = 'FEED_CLEAN_UP_FUTURE';
  const FEED_CLEAN_UP_ALL = 'FEED_CLEAN_UP_ALL';

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
   * @var User
   * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
   * @ORM\JoinColumn(referencedColumnName="id")
   */
  private $user;

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
   * @var bool
   *
   * @ORM\Column(type="boolean")
   */
  private $enabled = FALSE;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=255)
   */
  private $cleanUpStrategy = self::FEED_CLEAN_UP_NONE;

  /**
   * Get id.
   *
   * @return integer
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Set name.
   *
   * @param string $name
   *
   * @return Feed
   */
  public function setName($name) {
    $this->name = $name;

    return $this;
  }

  /**
   * Get name.
   *
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Set user.
   *
   * @param User $user
   *
   * @return Feed
   */
  public function setUser($user) {
    $this->user = $user;

    return $this;
  }

  /**
   * Get user.
   *
   * @return string
   */
  public function getUser() {
    return $this->user;
  }

  /**
   * Set configuration.
   *
   * @param array $configuration
   *
   * @return Feed
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;

    return $this;
  }

  /**
   * Get configuration.
   *
   * @return array
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  public function setCleanUpStrategy(string $cleanUpStrategy) {
    $this->cleanUpStrategy = $cleanUpStrategy;

    return $this;
  }

  public function getCleanUpStrategy() {
    return $this->cleanUpStrategy;
  }

  /**
   * Set lastRead.
   *
   * @param \DateTime $lastRead
   *
   * @return Feed
   */
  public function setLastRead($lastRead) {
    $this->lastRead = $lastRead;

    return $this;
  }

  /**
   * Get lastRead.
   *
   * @return \DateTime
   */
  public function getLastRead() {
    return $this->lastRead;
  }

  public function setEnabled($enabled) {
    $this->enabled = $enabled;
  }

  public function getEnabled() {
    return $this->enabled;
  }

  /**
   *
   */
  public function getUrl() {
    return isset($this->configuration['url']) ? $this->configuration['url'] : NULL;
  }

  /**
   *
   */
  public function getType() {
    return isset($this->configuration['type']) ? $this->configuration['type'] : NULL;
  }

  /**
   *
   */
  public function getRoot() {
    return isset($this->configuration['root']) ? $this->configuration['root'] : NULL;
  }

  /**
   *
   */
  public function getMapping() {
    return isset($this->configuration['mapping']) ? $this->configuration['mapping'] : NULL;
  }

  /**
   *
   */
  public function getBaseUrl() {
    return isset($this->configuration['baseUrl']) ? $this->configuration['baseUrl'] : NULL;
  }

  /**
   *
   */
  public function getDefaults() {
    return isset($this->configuration['defaults']) ? $this->configuration['defaults'] : NULL;
  }

  /**
   *
   */
  public function getTimeZone() {
    return isset($this->configuration['timeZone']) ? new \DateTimeZone($this->configuration['timeZone']) : NULL;
  }

  public function getDateFormat() {
    return isset($this->configuration['dateFormat']) ? $this->configuration['dateFormat'] : NULL;
  }

  public function __toString() {
    return $this->getName();
  }

}
