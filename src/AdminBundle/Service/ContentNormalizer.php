<?php

namespace AdminBundle\Service;

class ContentNormalizer implements ContentNormalizerInterface {
  /**
   * @var \HTMLPurifier
   */
  private $purifier;

  public function __construct(\HTMLPurifier $purifier) {
    $this->purifier = $purifier;
  }

  public function normalize(string $content): string {
    return $this->purifier->purify($content);
  }
}
