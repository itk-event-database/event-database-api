<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feed
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Feed
{
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="baseUrl", type="string", length=255)
     */
    private $baseUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="root", type="string", length=50)
     */
    private $root;

    /**
     * @var string
     *
     * @ORM\Column(name="mapping", type="text")
     */
    private $mapping;

    /**
     * @var string
     *
     * @ORM\Column(name="defaults", type="text")
     */
    private $defaults;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastRead", type="date")
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
     * Set url
     *
     * @param string $url
     * @return Feed
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set baseUrl
     *
     * @param string $baseUrl
     * @return Feed
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Get baseUrl
     *
     * @return string 
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Feed
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set root
     *
     * @param string $root
     * @return Feed
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root
     *
     * @return string 
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set mapping
     *
     * @param string $mapping
     * @return Feed
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * Get mapping
     *
     * @return string 
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Set defaults
     *
     * @param string $defaults
     * @return Feed
     */
    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Get defaults
     *
     * @return string 
     */
    public function getDefaults()
    {
        return $this->defaults;
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
}
