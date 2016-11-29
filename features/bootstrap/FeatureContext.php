<?php

use AppBundle\Entity\Tag;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\Tools\SchemaTool;
use AppBundle\Entity\User;
use AppBundle\Entity\Group;
use Sanpi\Behatch\Context\BaseContext;
use Sanpi\Behatch\Json\Json;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\HttpKernel\KernelInterface;
use Sanpi\Behatch\HttpCall\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends BaseContext implements Context, KernelAwareContext
{
  private $kernel;
  private $container;

  public function setKernel(KernelInterface $kernel)
  {
    $this->kernel = $kernel;
    $this->container = $this->kernel->getContainer();
  }

  /**
   * @var ManagerRegistry
   */
  private $doctrine;
  /**
   * @var \Doctrine\Common\Persistence\ObjectManager
   */
  private $manager;

  private $request;

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct(ManagerRegistry $doctrine, Request $request)
  {
    $this->doctrine = $doctrine;
    $this->manager = $doctrine->getManager();
    $this->schemaTool = new SchemaTool($this->manager);
    $this->classes = $this->manager->getMetadataFactory()->getAllMetadata();
    $this->request = $request;
  }

  /**
   * @BeforeScenario @createSchema
   */
  public function createDatabase()
  {
    $this->schemaTool->createSchema($this->classes);
  }

  /**
   * @AfterScenario @dropSchema
   */
  public function dropDatabase()
  {
    $this->schemaTool->dropSchema($this->classes);
  }

  private $users = [ // username, password, roles
    [ 'api-read', 'apipass', [ 'ROLE_API_READ' ], null ],
    [ 'api-write', 'apipass', [ 'ROLE_API_WRITE' ], null ],
    [ 'api-write2', 'apipass', [ 'ROLE_API_WRITE' ], null ],
    // [ 'user0-group0-write', 'apipass', [ 'ROLE_API_WRITE' ], [ 'group0'] ],
    // [ 'user1-group0-write', 'apipass', [ 'ROLE_API_WRITE' ], [ 'group0'] ],
    // [ 'user0-group1-write', 'apipass', [ 'ROLE_API_WRITE' ], [ 'group1'] ],
    // [ 'user1-group1-write', 'apipass', [ 'ROLE_API_WRITE' ], [ 'group1'] ],
  ];

  /** @BeforeScenario */
  public function createApiUsers(BeforeScenarioScope $scope) {
    // foreach ($this->groups as $data) {
    //   list($name, $roles) = $data;
    //   $group = new Group($name, $roles ?: []);
    //   $this->manager->persist($group);
    // }
    // $this->manager->flush();

    $groupRepository = $this->manager->getRepository(Group::class);

    foreach ($this->users as $data) {
      list($username, $password, $roles, $groups) = $data;
      $email = $username . '@example.com';
      $roles = $roles ?: [];
      $groups = $groups ?: [];

      $this->createUser($username, $email, $password, $roles, $groups);
    }
  }

  /** @AfterScenario */
  public function removeApiUsers(AfterScenarioScope $scope) {
    try {
      $userRepository = $this->manager->getRepository(User::class);
      $users = $userRepository->findBy(['username' => array_map(function($data) {
        return $data[0];
      }, $this->users)]);
      foreach ($users as $user) {
        foreach ($user->getGroups() as $group) {
          $this->manager->remove($group);
        }
        $this->manager->remove($user);
      }
      $this->manager->flush();
    } catch (TableNotFoundException $ex) {
      // The table may no longer exist.
    }
  }

  /** @AfterScenario */
  public function signOut(AfterScenarioScope $scope) {
    $this->removeAuthenticationHeader();
  }

  /**
   * @When I sign in with username :username and password :password
   */
  public function iSignInWithUsernameAndPassword($username, $password)
  {
    $user = $this->getUser($username);

    if ($user) {
      $encoder_service = $this->container->get('security.encoder_factory');
      $encoder = $encoder_service->getEncoder($user);
      if ($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
        $this->addAuthenticationHeader($user);
      }
    } else {
      $this->removeAuthenticationHeader();
    }
  }

  /**
   * @When I authenticate as :username
   */
  public function iAuthenticateAs($username)
  {
    $user = $this->getUser($username);
    if ($user) {
      $this->addAuthenticationHeader($user);
    } else {
      $this->removeAuthenticationHeader();
    }
  }

  /**
   * @Then the JSON should not differ from:
   */
  public function theJsonShouldNotDifferFrom(PyStringNode $content) {
    $actual = $this->getJson();

    try {
      $expected = new Json($content);
    }
    catch (\Exception $e) {
      throw new \Exception('The expected JSON is not a valid');
    }

    try {
      $this->assertSame(
        (string)$expected,
        (string)$actual,
        "The json is equal to:\n" . $actual->encode()
      );
    } catch (ExpectationException $ex) {
      $differ = new Differ("--- Expected\n+++ Actual\n", true);
      $message = $differ->diff($expected->encode(), $actual->encode());
      throw new ExpectationException($message, $this->getSession(), $ex);
    }
  }

  /**
   * @Given the following tags exist:
   */
  public function theFollowingTagsExist(TableNode $table) {
    $tagManager = $this->container->get('tag_manager');
    $tagManager->setTagNormalizer(null);
    $names = array_map(function ($row) {
      return $row['name'];
    }, $table->getHash());
    $tagManager->loadOrCreateTags($names);
  }

  /**
   * @Given the following tags are unknown:
   */
  public function theFollowingTagsAreUnknown(TableNode $table) {
    $unknownTagManager = $this->container->get('unknown_tag_manager');
    $unknownTagManager->setTagNormalizer(null);
    $names = array_map(function ($row) {
      return $row['name'];
    }, $table->getHash());
    $tags = $unknownTagManager->loadOrCreateTags($names);
    $unknownTags = [];
    foreach ($tags as $tag) {
      $unknownTags[$tag->getName()] = $tag;
    }
    $tagManager = $this->container->get('tag_manager');

    $em = $this->container->get('doctrine.orm.default_entity_manager');
    foreach ($table->getHash() as $row) {
      $unknownName = $row['name'];
      $name = $row['tag'];
      $unknownTag = $unknownTagManager->loadTags([$unknownName])[0];
      $knownTag = $tagManager->loadTags([$name])[0];
      $unknownTag->setTag($knownTag);
      $em->persist($unknownTag);
      $em->flush();
    }
  }

  /**
   * @Given the following users exist:
   */
  public function theFollowingUsersExist(TableNode $table) {
    foreach ($table->getHash() as $row) {
      $username = $row['username'];
      $email = $username . '@example.com';
      $password = isset($row['password']) ? $row['password'] : uniqid();
      $roles = isset($row['roles']) ? preg_split('/\s*,\s*/', $row['roles'], -1, PREG_SPLIT_NO_EMPTY) : [];
      $groups = isset($row['groups']) ? preg_split('/\s*,\s*/', $row['groups'], -1, PREG_SPLIT_NO_EMPTY) : [];

      $this->createUser($username, $email, $password, $roles, $groups);
    }
  }

  private function createUser(string $username, string $email, string $password, array $roles, array $groups) {
    $groups = $this->createGroups($groups);

    $userManager = $this->container->get('fos_user.user_manager');

    $user = $userManager->findUserBy(['username' => $username ]);
    if (!$user) {
      $user = $userManager->createUser();
    }
    $user
      ->setEnabled(true)
      ->setUsername($username)
      ->setPlainPassword($password)
      ->setEmail($email)
      ->setRoles($roles);

    foreach ($groups as $group) {
      $user->addGroup($group);
    }
    $userManager->updateUser($user);
  }

  private function createGroups(array $names, array $roles = null) {
    $groups = new ArrayCollection();
    $repository = $this->manager->getRepository(Group::class);

    foreach ($names as $name) {
      $group = $repository->findOneBy(['name' => $name]);
      if (!$group) {
        $group = new Group($name, $roles ?: []);
        $this->manager->persist($group);
        $this->manager->flush();
      }
      $groups[] = $group;
    }

    return $groups;
  }

  protected function getJson()
  {
    return new Json($this->request->getContent());
  }

  /**
   * Get a user by username.
   *
   * @param $username
   * @return User|null
   */
  private function getUser($username)
  {
    $repository = $this->manager->getRepository(User::class);
    return $repository->findOneBy(['username' => $username]);
  }

  /**
   * Add authentication header to request.
   */
  private function addAuthenticationHeader(User $user) {
    // @see https://github.com/Behat/Behat/issues/901
    $token = $this->container->get('lexik_jwt_authentication.encoder')
           ->encode(['username' => $user->getUsername()]);

    $this->request->setHttpHeader('Authorization', 'Bearer ' . $token);
  }

  private function removeAuthenticationHeader() {
    $this->request->setHttpHeader('Authorization', '');
  }
}
