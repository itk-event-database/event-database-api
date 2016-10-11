<?php

namespace AdminBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

class TagNormalizer implements TagNormalizerInterface {
  /**
   * @var \Doctrine\ORM\EntityManagerInterface
   */
  private $entityManager;

  public function __construct(EntityManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
  }

  public function normalize(string $name) {
    return strtolower($name);
  }
}