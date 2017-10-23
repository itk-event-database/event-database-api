<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\UnknownTag.
 *
 * @ORM\Table()
 *
 * @ORM\Entity()
 */
class UnknownTag extends BaseTag
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Tag
     * @ORM\ManyToOne(targetEntity="Tag")
     */
    protected $tag;

    /**
     *
     */
    public function setTag(Tag $tag = null)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     *
     */
    public function getTag()
    {
        return $this->tag;
    }
}
