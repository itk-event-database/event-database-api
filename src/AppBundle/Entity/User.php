<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 */
class User extends BaseUser {
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Group")
   * @ORM\JoinTable(name="fos_user_user_group",
   *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
   *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
   * )
   */
  protected $groups;

  /**
   * @var string The username of the author.
   *
   * @Groups({"user_read", "user_write"})
   */
  protected $username;

  /**
   * @var string The email of the user.
   *
   * @Groups({"user_read", "user_write"})
   */
  protected $email;

  /**
   * @var string Plain password. Used for model validation. Must not be persisted.
   *
   * @Groups({"user_write"})
   */
  protected $plainPassword;

  /**
   * @var boolean Shows that the user is enabled
   *
   * @Groups({"user_read", "user_write"})
   */
  protected $enabled;

  /**
   * @var array Array, role(s) of the user
   *
   * @Groups({"user_read", "user_write"})
   */
  protected $roles;

}
