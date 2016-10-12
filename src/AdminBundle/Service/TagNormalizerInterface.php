<?php
/**
 * Created by PhpStorm.
 * User: rimi
 * Date: 06/10/2016
 * Time: 13.54
 */

namespace AdminBundle\Service;

interface TagNormalizerInterface {
  public function normalize(string $tag);
}