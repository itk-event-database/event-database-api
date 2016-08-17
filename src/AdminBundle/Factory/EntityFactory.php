<?php
/**
 * Created by PhpStorm.
 * User: turegjorup
 * Date: 11/08/16
 * Time: 15:57
 */

namespace AdminBundle\Factory;


use Doctrine\ORM\EntityManager;

abstract class EntityFactory
{
  protected $em;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

}