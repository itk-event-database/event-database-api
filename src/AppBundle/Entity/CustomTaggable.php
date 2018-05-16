<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Entity;

use DoctrineExtensions\Taggable\Taggable;

interface CustomTaggable extends Taggable
{
    /**
     * @param string[] $customTags
     *
     * @return CustomTaggable
     */
    public function setCustomTags(array $customTags);

    /**
     * @return string[]
     */
    public function getCustomTags();
}
