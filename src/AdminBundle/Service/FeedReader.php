<?php

namespace AdminBundle\Service;

use AdminBundle\Entity\Feed;
use AdminBundle\Service\FeedReader\Controller;
use AdminBundle\Service\FeedReader\EventImporter;
use AdminBundle\Service\FeedReader\ValueConverter;
use AppBundle\Entity\User;
use Gedmo\Blameable\BlameableListener;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class FeedReader implements Controller {
  /**
   * @var ValueConverter
   */
  protected $valueConverter;

  /**
   * @var EventImporter
   */
  protected $eventImporter;

  /**
   * @var array
   */
  protected $configuration;

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * @var AuthenticatorService
   */
  protected $authenticator;

  /**
   * @var BlameableListener
   */
  protected $blameableListener;

  /**
   * @var Feed
   */
  protected $feed;

  /**
   * @var OutputInterface
   */
  protected $output;

  /**
   * @param \AdminBundle\Service\FeedReader\ValueConverter $valueConverter
   * @param \AdminBundle\Service\FeedReader\EventImporter $eventImporter
   * @param array $configuration
   * @param \Psr\Log\LoggerInterface $logger
   * @param \AdminBundle\Service\AuthenticatorService $authenticator
   * @param \Gedmo\Blameable\BlameableListener $blameableListener
   */
  public function __construct(ValueConverter $valueConverter, EventImporter $eventImporter, array $configuration, LoggerInterface $logger, AuthenticatorService $authenticator, BlameableListener $blameableListener) {
    $this->valueConverter = $valueConverter;
    $this->eventImporter = $eventImporter;
    $this->configuration = $configuration;
    $this->logger = $logger;
    $this->authenticator = $authenticator;
    $this->blameableListener = $blameableListener;
  }

  /**
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   * @return $this
   */
  public function setOutput(OutputInterface $output) {
    $this->output = $output;

    return $this;
  }

  /**
   * @param \AdminBundle\Entity\Feed $feed
   * @param \AppBundle\Entity\User $user
   */
  public function read(Feed $feed, User $user = NULL) {
    $this->feed = $feed;
    if (!$user) {
      $user = $this->feed->getUser();
    }
    if (!$user) {
      throw new \Exception('No user on feed.');
    }
    if ($this->authenticator) {
      $this->authenticator->authenticate($user);
    }
    $this->eventImporter
      ->setFeed($feed)
      ->setUser($user)
      ->setLogger($this->logger);
    $this->valueConverter->setFeed($feed);

    if ($user) {
      // Tell Blameable which user is creating entities.
      if ($this->blameableListener) {
        $this->blameableListener->setUserValue($user);
      }
    }

    $reader = $this->getReader();
    $content = $this->getContent();
    if (!$content) {
      return;
    }
    $reader->read($content);
  }

  /**
   *
   */
  private function getReader() {
    $readers = isset($this->configuration['readers']) ? $this->configuration['readers'] : [];
    $type = $this->feed->getType();

    if (!isset($readers[$type])) {
      throw new \Exception('Unknown feed type: ' . $type);
    }

    $reader = $readers[$type];
    $reader
      ->setController($this)
      ->setFeed($this->feed);

    return $reader;
  }

  /**
   *
   */
  protected function getContent() {
    $client = new Client();
    $feedUrl = $this->processUrl($this->feed->getUrl());

    $configuration = $this->feed->getConfiguration();
    $method = isset($configuration['method']) ? $configuration['method'] : 'GET';
    $options = isset($configuration['options']) ? $configuration['options'] : [];

    $res = $client->request($method, $feedUrl, $options);
    if ($res->getStatusCode() !== 200) {
      return NULL;
    }

    $content = $res->getBody();
    // http://stackoverflow.com/questions/10290849/how-to-remove-multiple-utf-8-bom-sequences-before-doctype
    $bom = pack('H*', 'EFBBBF');
    $content = preg_replace("/^$bom/", '', $content);

    $type = $this->feed->getType();
    switch ($type) {
      case 'json':
        $content = json_decode($content, TRUE);
        break;

      case 'xml':
        $content = new \SimpleXmlElement($content);
        break;
    }

    return $content;
  }

  /**
   * @param $url
   * @return
   */
  private function processUrl($url) {
    return $url;
  }

  /**
   * @param array $data
   */
  public function createEvent(array $data) {
    $data['feed'] = $this->feed;
    $data['feed_event_id'] = $data['id'];
    $event = $this->eventImporter->import($data);
    if ($event) {
      $status = ($event->getUpdatedAt() > $event->getCreatedAt()) ? 'updated' : 'created';
      $this->writeln(sprintf('% 8d %s: Event %s: %s (%s)', $this->feed->getId(), $this->feed->getName(), $status, $event->getName(), $event->getFeedEventId()));
    }
    else {
      $this->writeln(sprintf('Cannot import event: id: %s; feed: %s', var_export($data['id'], TRUE), $this->feed->getName()));
    }
  }

  /**
   * @param $value
   * @param $name
   * @return \DateTime|null|string
   */
  public function convertValue($value, $name) {
    return $this->valueConverter->convert($value, $name);
  }

  /**
   * @param $messages
   * @param int $options
   */
  public function writeln($messages, $options = 0) {
    if ($this->output) {
      $this->output->writeln($messages, $options);
    }
  }

}
