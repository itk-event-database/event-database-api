<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;
use AppBundle\Entity\User;
use Symfony\Component\HttpKernel\KernelInterface;
use Sanpi\Behatch\HttpCall\Request;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext, KernelAwareContext
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

    /** @BeforeScenario */
    public function createApiUsers(BeforeScenarioScope $scope)
    {
        $users = [ // username, email, password, roles
            [ 'api-read', 'api-read@example.com', 'apipass', [ 'ROLE_API_READ' ] ],
            [ 'api-write', 'api-write@example.com', 'apipass', [ 'ROLE_API_WRITE' ] ],
        ];

        $repository = $this->manager->getRepository(User::class);

        foreach ($users as $data) {
            list($username, $email, $password, $roles) = $data;
            $user = $repository->findOneBy([ 'username' => $username ]);
            if ($user == null) {
                $user = new User();
                $user
                    ->setUsername($username)
                    ->setPlainPassword($password)
                    ->setEmail($email)
                    ->setRoles($roles);
                $this->manager->persist($user);
                $this->manager->flush();
            }
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
     * Get a user by username.
     *
     * @param $username
     * @return User|null
     */
    private function getUser($username)
    {
        $repository = $this->manager->getRepository(User::class);
        return $repository->findOneBy([ 'username' => $username ]);
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
