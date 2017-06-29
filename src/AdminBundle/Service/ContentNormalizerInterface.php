<?php

namespace AdminBundle\Service;

/**
 *
 */
interface ContentNormalizerInterface
{

  /**
   * Normalize html content.
   *
   * @param string $content
   *   The html string.
   *
   * @return string
   *   Normalized and cleaned html.
   */
    public function normalize($content);
}
