<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 *
 */
class FeedControllerTest extends WebTestCase  {
  private $client = null;

  public function setUp() {
    $this->client = static::createClient();
  }

  public function testCompleteScenario() {
    $this->signIn();

    $crawler = $this->client->request('GET', '/');
    $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /admin/feed/");
    // $crawler = $this->client->click($crawler->selectLink('Create new feed')->link());

    // // Create a new entry in the database
    $crawler = $this->client->request('GET', '/admin/feed/');
    $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /admin/feed/");
    $crawler = $this->client->click($crawler->selectLink('Create new feed')->link());

    // Fill in the form and submit it
    $form = $crawler->selectButton('Create')->form([
      'feed[name]' => 'Test',
      'feed[configuration]' => json_encode(['type' => 'json']),
    ]);

    $this->client->submit($form);
    $crawler = $this->client->followRedirect();

    // // Check data in the show view
    // $this->assertGreaterThan(0, $crawler->filter('td:contains("Test")')->count(), 'Missing element td:contains("Test")');

    // // Edit the entity
    // $crawler = $this->client->click($crawler->selectLink('Edit')->link());

    // $form = $crawler->selectButton('Update')->form([
    //   'appbundle_feed[field_name]'  => 'Foo',
    //   // ... other fields to fill
    // ]);

    // $this->client->submit($form);
    // $crawler = $this->client->followRedirect();

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

    $token = new UsernamePasswordToken('admin', null, $firewall, ['ROLE_SUPER_ADMIN']);
    $session->set('_security_'.$firewall, serialize($token));
    $session->save();

    $cookie = new Cookie($session->getName(), $session->getId());
    $this->client->getCookieJar()->set($cookie);
  }
}
