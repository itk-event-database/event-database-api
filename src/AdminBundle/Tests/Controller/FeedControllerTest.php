<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\AppBundle\Test\DatabaseWebTestCase;

/**
 *
 */
class FeedControllerTest extends DatabaseWebTestCase {
  /**
   * @var \Symfony\Bundle\FrameworkBundle\Client
   */
  private $client = NULL;

  public function setUp() {
    $this->client = static::createClient();
  }

  public function testCompleteScenario() {
    $this->signIn();

    $crawler = $this->client->request('GET', '/');
    $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");

    // // Create a new entry in the database
    $crawler = $this->client->request('GET', '/admin/feed/');
    $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /admin/feed/");
    $this->assertEquals(1, $crawler->filter('h1:contains("Feed list")')->count(), 'Cannot find page title with content "Feed list"');
    $this->assertEquals(1, $crawler->selectLink('Create new feed')->count(), 'Cannot find link with text "Create new feed"');
    $crawler = $this->client->click($crawler->selectLink('Create new feed')->link());

    // Fill in the form and submit it
    $form = $crawler->selectButton('Create')->form([
      'feed[name]' => 'Test',
      'feed[configuration]' => json_encode(['type' => 'json']),
    ]);
    $this->client->submit($form);
    $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    $crawler = $this->client->followRedirect();

    $this->assertEquals(1, $crawler->filter('.alert-success:contains("Feed Test created")')->count(), 'Cannot find success alert with content "Feed Test created"');
    $this->assertEquals(1, $crawler->filter('h1:contains("Feed")')->count(), 'Cannot find page title with content "Feed"');
    // Edit the entity
    $crawler = $this->client->click($crawler->selectLink('Edit')->link());

    $this->assertEquals(1, $crawler->filter('h1:contains("Edit feed")')->count(), 'Cannot find page title with content "Edit feed"');

    $form = $crawler->selectButton('Save')->form([
      'feed[name]' => 'Test (updated)',
    ]);
    $this->client->submit($form);
    $crawler = $this->client->followRedirect();

    $this->assertEquals(1, $crawler->filter('.alert-success:contains("Feed Test (updated) updated")')->count(), 'Cannot find success alert');
    $this->assertEquals(1, $crawler->filter('h1:contains("Edit feed")')->count(), 'Cannot find page title with content "Edit feed"');

    $crawler = $this->client->request('GET', '/admin/feed/');

    // Two table rows: Header and content
    $this->assertEquals(2, $crawler->filter('tr')->count(), 'Cannot find header and content row in table');
    $this->assertEquals('Test (updated)', $crawler->filter('tr > td:first-child')->text());

    $crawler = $this->client->click($crawler->selectLink('Test (updated)')->link());

    // // Check the element contains an attribute with value equals "Foo"
    // $this->assertGreaterThan(0, $crawler->filter('[value="Foo"]')->count(), 'Missing element [value="Foo"]');

    // // Delete the entity
    // $this->client->submit($crawler->selectButton('Delete')->form());
    // $crawler = $this->client->followRedirect();

    // // Check the entity has been delete on the list
    // $this->assertNotRegExp('/Foo/', $this->client->getResponse()->getContent());
  }

  private function signIn() {
    $session = $this->client->getContainer()->get('session');

    // the firewall context (defaults to the firewall name)
    $firewall = 'main';

    $user = new User();
    $user
      ->setUsername('admin')
      ->setPlainPassword('password')
      ->setEmail('admin@example.com')
      ->setRoles(['ROLE_SUPER_ADMIN']);
    static::$em->persist($user);
    static::$em->flush($user);

    $token = new UsernamePasswordToken($user, NULL, $firewall, ['ROLE_SUPER_ADMIN']);
    $session->set('_security_' . $firewall, serialize($token));
    $session->save();

    $cookie = new Cookie($session->getName(), $session->getId());
    $this->client->getCookieJar()->set($cookie);
  }

}
