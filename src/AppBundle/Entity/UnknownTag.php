<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use FPN\TagBundle\Entity\Tag as BaseTag;
use Symfony\Component\Serializer\Annotation\Groups;
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
