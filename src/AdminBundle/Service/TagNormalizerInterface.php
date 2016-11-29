<?php

namespace AdminBundle\Service;

/**
 *
 */
interface TagNormalizerInterface {

  /**
   * @param array $names
   * @return
   */
  public function normalize(array $names);

}
