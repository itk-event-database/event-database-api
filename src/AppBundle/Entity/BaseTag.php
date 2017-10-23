<?php

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
