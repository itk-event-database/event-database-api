<?php

namespace AppBundle\Entity;

/**
 *
 */
abstract class Entity {
  private $skipImport = FALSE;

  public function setSkipImport($skipImport) {
    $this->skipImport = $skipImport;

    return $this;
  }

  public function getSkipImport() {
    return $this->skipImport;
  }
}
