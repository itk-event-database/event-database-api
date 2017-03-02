<?php

namespace AdminBundle\EventSubscriber;

use AppBundle\Entity\Thing;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 *
 */
class UploadsSubscriber implements EventSubscriber {
  protected $configuration;

  public function __construct(array $configuration = []) {
    $this->configuration = $configuration;
  }

  public function getSubscribedEvents() {
    return [
      Events::prePersist,
      Events::preUpdate,
    ];
  }

  public function prePersist(LifecycleEventArgs $args) {
    $this->handleUploadedFile($args);
  }

  public function preUpdate(LifecycleEventArgs $args) {
    $this->handleUploadedFile($args);
  }

  public function handleUploadedFile(LifecycleEventArgs $args) {
    $object = $args->getObject();
    if ($object instanceof Thing) {
      if ($object->getImageFile()) {
        $file = $object->getImageFile();

        $imageUrl = trim($this->configuration['base_url'], '/'). '/'
          . $this->configuration['files']['url'] . $file->getFilename();
        $object->setImage($imageUrl);
      }
    }
  }
}
