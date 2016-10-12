<?php
/**
 * Created by PhpStorm.
 * User: rimi
 * Date: 06/10/2016
 * Time: 13.54
 */

namespace AdminBundle\Service;

use AppBundle\Entity\TagManager;

interface TagNormalizerInterface {
  public function normalize(array $names);
}
