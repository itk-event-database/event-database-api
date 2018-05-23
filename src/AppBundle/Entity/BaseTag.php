<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Entity;

use FPN\TagBundle\Entity\Tag;
use Gedmo\Mapping\Annotation as Gedmo;

class BaseTag extends Tag
{
    /**
     * @Gedmo\Slug(fields={"name"})
     */
    protected $slug;
}
