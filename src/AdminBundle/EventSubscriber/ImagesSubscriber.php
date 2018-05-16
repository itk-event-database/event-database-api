<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\EventSubscriber;

use AdminBundle\Service\ImageGenerator;
use AppBundle\Entity\Thing;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class ImagesSubscriber implements EventSubscriber
{
    /** @var \AdminBundle\Service\ImageGenerator */
    private $imageGenerator;

    /** @var array */
    private $configuration;

    public function __construct(ImageGenerator $imageGenerator, array $configuration = [])
    {
        $this->imageGenerator = $imageGenerator;
        $this->configuration = $configuration;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->handleUploadedFile($args);
        $this->setImages($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->handleUploadedFile($args);
        $this->setImages($args);
    }

    private function handleUploadedFile(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if ($object instanceof Thing) {
            if ($object->getImageFile()) {
                $file = $object->getImageFile();

                $imageUrl = trim($this->configuration['base_url'], '/').'/'
                .$this->configuration['files']['url'].$file->getFilename();
                $object->setImage($imageUrl);
            }
        }
    }

    private function setImages(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if ($object instanceof Thing) {
            $this->imageGenerator->setImages($object);
        }
    }
}
