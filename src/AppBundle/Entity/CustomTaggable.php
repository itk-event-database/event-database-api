<?php

namespace AppBundle\Entity;

use DoctrineExtensions\Taggable\Taggable;

interface CustomTaggable extends Taggable
{

  /**
   * @param string[] $customTags
   * @return CustomTaggable
   */
    public function setCustomTags(array $customTags);

  /**
   * @return string[]
   */
    public function getCustomTags();
}
