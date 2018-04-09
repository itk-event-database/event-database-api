<?php

namespace AppBundle\Features\Context;

use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behatch\Context\BaseContext;
use Behatch\HttpCall\HttpCallResultPool;
use Behatch\HttpCall\Request;
use Behatch\Json\Json;
use Behatch\Json\JsonInspector;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

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

    /** @var JsonInspector */
    private $inspector;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct(
        ManagerRegistry $doctrine,
        Request $request,
        HttpCallResultPool $httpCallResultPool,
        $evaluationMode = 'javascript'
    ) {
        $this->doctrine = $doctrine;
        $this->manager = $doctrine->getManager();
        $this->schemaTool = new SchemaTool($this->manager);
        $this->classes = $this->manager->getMetadataFactory()->getAllMetadata();
        $this->request = $request;
        $this->inspector = new JsonInspector($evaluationMode);
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

    /** @AfterScenario */
    public function signOut(AfterScenarioScope $scope)
    {
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
    public function theJsonShouldNotDifferFrom(PyStringNode $content)
    {
        $actual = $this->getJson();

        try {
            $expected = new Json($content);
        } catch (\Exception $e) {
            throw new \Exception('The expected JSON is not a valid');
        }

        try {
            $this->assertSame(
                (string) $expected,
                (string) $actual,
                "The json is equal to:\n".$actual->encode()
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
    public function theFollowingTagsExist(TableNode $table)
    {
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
    public function theFollowingTagsAreUnknown(TableNode $table)
    {
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
    public function theFollowingUsersExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $username = $row['username'];
            $email = $username.'@example.com';
            $password = isset($row['password']) ? $row['password'] : uniqid();
            $roles = isset($row['roles']) ? preg_split('/\s*,\s*/', $row['roles'], -1, PREG_SPLIT_NO_EMPTY) : [];
            $groups = isset($row['groups']) ? preg_split('/\s*,\s*/', $row['groups'], -1, PREG_SPLIT_NO_EMPTY) : [];

            $this->createUser($username, $email, $password, $roles, $groups);
        }
    }

    /**
     * @Given /^the following (?P<entityClass>.+) entities(?: identified by (?P<idColumn>.+))? exist:$/
     *
     * @param mixed $type
     */
    public function theFollowingEntitiesExist($entityClass, $idColumn = 'id', TableNode $table = null)
    {
        $entityClass = trim($entityClass, '\'"');
        $idColumn = trim($idColumn, '\'"');
        if (!class_exists($entityClass)) {
            throw new \RuntimeException('Class '.$entityClass.' does not exist.');
        }

        $repository = $this->manager->getRepository($entityClass);
        $accessor = $this->container->get('property_accessor');
        foreach ($table->getHash() as $row) {
            if ($row[$idColumn] && $repository->find($row[$idColumn]) !== null) {
                continue;
            }
            $entity = new $entityClass();
            foreach ($row as $path => $value) {
                if ($path === $idColumn) {
                    $property = new \ReflectionProperty(get_class($entity), $idColumn);
                    $property->setAccessible(true);
                    $property->setValue($entity, $value);
                } else {
                    $accessor->setValue($entity, $path, $value);
                }
            }
            $this->persist($entity);
        }
    }

    private function createUser(string $username, string $email, string $password, array $roles, array $groups)
    {
        $groups = $this->createGroups($groups);

        $userManager = $this->container->get('fos_user.user_manager');

        $user = $userManager->findUserBy(['username' => $username]);
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

    private function createGroups(array $names, array $roles = null)
    {
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
     *
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
    private function addAuthenticationHeader(User $user)
    {
        // @see https://github.com/Behat/Behat/issues/901
        $token = $this->container->get('lexik_jwt_authentication.encoder')
           ->encode(['username' => $user->getUsername()]);

        $this->request->setHttpHeader('Authorization', 'Bearer '.$token);
    }

    private function removeAuthenticationHeader()
    {
        $this->request->setHttpHeader('Authorization', '');
    }

    /**
     * @Then the SQL query :sql should return :count element(s)
     */
    public function theSqlQueryShouldReturnElements($sql, $count)
    {
        $stmt = $this->manager->getConnection()->prepare($sql);
        $stmt->execute();
        $items = $stmt->fetchAll();

        $this->assertEquals($count, count($items));
    }

    /**
     * @Then print result of :sql
     */
    public function printResultOfSql($sql)
    {
        $stmt = $this->manager->getConnection()->prepare($sql);
        $stmt->execute();
        $items = $stmt->fetchAll();

        $rows = [];
        foreach ($items as $index => $item) {
            if ($index === 0) {
                $rows[$index + 1] = array_keys($item);
            }
            $rows[$index + 2] = array_values($item);
        }

        // TableNode cannot handle null values.
        foreach ($rows as &$row) {
            $row = array_map(function ($value) {
                return $value === null ? '(null)' : $value;
            }, $row);
        }

        $table = new TableNode($rows);
        echo $table->getTableAsString();
    }

    /**
     * Checks that a list of elements contains a specific number of nodes matching a criterion.
     *
     * @Then the JSON node :node should contain :count element(s) with :propertyPath equal to :value
     *
     * @param mixed $node
     * @param mixed $count
     * @param mixed $propertyPath
     * @param mixed $value
     */
    public function theJsonNodeShouldContainElementWithEqualTo($node, $count, $propertyPath, $value)
    {
        $json = $this->getJson();
        $items = $this->inspector->evaluate($json, $node);
        $this->assertTrue(is_array($items), sprintf('The node "%s" should be an array', $node));

        // The property_accessor service caches property paths, but '@id' is not a valid cache key.
        // Therefore we create our own property accessor.
        $accessor = PropertyAccess::createPropertyAccessor();
        $matches = array_filter($items, function ($item) use ($propertyPath, $value, $accessor) {
            return $accessor->isReadable($item, $propertyPath) && $accessor->getValue($item, $propertyPath) === $value;
        });
        $this->assertSame($count, count($matches));
    }

    protected function persist($entity)
    {
        $metadata = null;
        $idGenerator = null;
        $idGeneratorType = null;
        if ($entity->getId() !== null) {
            // Remove id generator and set id manually.
            $metadata = $this->manager->getClassMetadata(get_class($entity));
            $idGenerator = $metadata->idGenerator;
            $idGeneratorType = $metadata->generatorType;
            $metadata->setIdGeneratorType($metadata::GENERATOR_TYPE_NONE);
        }

        $this->manager->persist($entity);
        // We need to flush to force the id to be set.
        $this->manager->flush();

        // Restore id generator.
        if ($metadata !== null) {
            $metadata->setIdGenerator($idGenerator);
            $metadata->setIdGeneratorType($idGeneratorType);
        }
    }
}
