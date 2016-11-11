<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\GroupRepository")
 * @ORM\Table(name="fos_group")
 */
class Group extends BaseGroup {
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @ORM\ManyToMany(targetEntity="User", mappedBy="groups")
   **/
  protected $users;

  public function __construct($name, $roles = array()) {
    parent::__construct($name, $roles);
  }

  /**
   * Get users
   *
   * @return \Doctrine\Common\Collections\Collection
   */
  public function getUsers() {
    return $this->users ?: $this->users = new ArrayCollection();
  }
}
