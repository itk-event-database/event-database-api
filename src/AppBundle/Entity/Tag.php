<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use FPN\TagBundle\Entity\Tag as BaseTag;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\Tag.
 *
 * @ORM\Table()
 *
 * @ORM\Entity(repositoryClass="DoctrineExtensions\Taggable\Entity\TagRepository")
 *
 * @ApiResource(
 *   collectionOperations={
 *     "get"={"method"="GET"}
 *   },
 *   itemOperations={
 *     "get"={"method"="GET"}
 *   },
 *   attributes = {
 *     "jsonld_embed_context" = true,
 *     "normalization_context" = { "groups" = { "read" } },
 *     "denormalization_context" = { "groups" = { "read" } },
 *   }
 * )
 */
class Tag extends BaseTag
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
   * @var string
   * @Groups({"read"})
   */
    protected $name;

  /**
   * @var Tagging
   * @ORM\OneToMany(targetEntity="Tagging", mappedBy="tag", fetch="EAGER")
   **/
    protected $tagging;
}
