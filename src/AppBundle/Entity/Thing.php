<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * The most generic type of item.
 *
 * @see http://schema.org/Thing Documentation on Schema.org
 *
 * @ORM\MappedSuperclass
 * @ ApiResource(iri="http://schema.org/Thing")
 * @Vich\Uploadable
 */
abstract class Thing extends Entity
{
    /**
     * @var string a short description of the item
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="https://schema.org/description")
     */
    private $description;

    /**
     * @var string
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/image")
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/image")
     */
    private $originalImage;

    /**
     * @var string the name of the item
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="https://schema.org/name")
     */
    private $name;

    /**
     * @var string the URI of the item
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/url")
     */
    private $url;

    /**
     * @var string The video (Youtube/Vimeo/etc.) URI of the item.
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/url")
     */
    private $videoUrl;

    /**
     * @var string the language code of the item
     *
     * @Groups({"event_read", "occurrence_read", "event_write"})
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/langcode")
     */
    private $langcode;

    /**
     * @see https://github.com/javiereguiluz/EasyAdminBundle/blob/master/Resources/doc/tutorials/upload-files-and-images.md
     */

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     */
    private $imagePath;

    /**
     * @Vich\UploadableField(mapping="thing_images", fileNameProperty="imagePath")
     *
     * @var File
     */
    private $imageFile;

    /**
     * Sets description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets image.
     *
     * @param string $image
     *
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Gets image.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Sets originalImage.
     *
     * @param string $originalImage
     *
     * @return $this
     */
    public function setOriginalImage($originalImage)
    {
        $this->originalImage = $originalImage;

        return $this;
    }

    /**
     * Gets originalImage.
     *
     * @return string
     */
    public function getOriginalImage()
    {
        return $this->originalImage;
    }

    /**
     * Sets name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets url.
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Gets url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets langcode.
     *
     * @param string $langcode
     *
     * @return $this
     */
    public function setLangcode($langcode)
    {
        $this->langcode = $langcode;

        return $this;
    }

    /**
     * Gets langcode.
     *
     * @return string
     */
    public function getLangcode()
    {
        return $this->langcode;
    }

    /**
     * Gets videoURL.
     *
     * @return string
     */
    public function getVideoUrl()
    {
        return $this->videoUrl;
    }

    /**
     * Sets videoURL.
     *
     * @param string $videoUrl
     */
    public function setVideoUrl($videoUrl)
    {
        $this->videoUrl = $videoUrl;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    }

    public function getImagePath()
    {
        return $this->imagePath;
    }

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            $this->originalImage = 'upload://'.$image->getFilename();
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }
}
