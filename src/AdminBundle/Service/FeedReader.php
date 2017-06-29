<?php

namespace AdminBundle\Service;

use AdminBundle\Entity\Feed;
use AdminBundle\Service\FeedReader\Controller;
use AdminBundle\Service\FeedReader\EventImporter;
use AdminBundle\Service\FeedReader\ValueConverter;
use AppBundle\Entity\Event;
use AppBundle\Entity\User;
use Gedmo\Blameable\BlameableListener;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class FeedReader implements Controller
{
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
   * @var ManagerRegistry
   */
    protected $managerRegistry;

  /**
   * @var Feed
   */
    protected $feed;

  /**
   * @var array
   */
    protected $feedEventIds;

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
   * @param \Symfony\Bridge\Doctrine\ManagerRegistry $managerRegistry
   * @param \AdminBundle\Service\FeedManager $feedManager
   */
    public function __construct(ValueConverter $valueConverter, EventImporter $eventImporter, array $configuration, LoggerInterface $logger, AuthenticatorService $authenticator, BlameableListener $blameableListener, ManagerRegistry $managerRegistry, FeedManager $feedManager = null)
    {
        $this->valueConverter = $valueConverter;
        $this->eventImporter = $eventImporter;
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->authenticator = $authenticator;
        $this->blameableListener = $blameableListener;
        $this->managerRegistry = $managerRegistry;
        $this->feedManager = $feedManager;
    }

  /**
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   * @return $this
   */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

  /**
   * @param \AdminBundle\Entity\Feed $feed
   * @param \AppBundle\Entity\User $user
   */
    public function read(Feed $feed, User $user = null, bool $cleanUpEvents = false)
    {
        $this->feed = $feed;
        $this->feedEventIds = [];
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

        $connection = $this->managerRegistry->getConnection();
        $connection->beginTransaction();
        try {
            $this->cleanUpEvents = null;
            if ($cleanUpEvents) {
                $this->cleanUpEvents = $this->feedManager->getCleanUpEvents($feed);
            }
            $reader->read($content);
            if ($this->cleanUpEvents !== null) {
                $this->feedManager->cleanUpEvents($feed, $this->cleanUpEvents);
            }
            $connection->commit();
        } catch (\Throwable $t) {
            $connection->rollBack();
            throw $t;
        }
    }

  /**
   * @var array|null
   */
    private $cleanUpEvents;

    private function keepEvent(Event $event)
    {
        unset($this->cleanUpEvents[$event->getId()]);
    }

  /**
   *
   */
    private function getReader()
    {
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
    protected function getContent()
    {
        $client = new Client();
        $feedUrl = $this->processUrl($this->feed->getUrl());

        $configuration = $this->feed->getConfiguration();
        $method = isset($configuration['method']) ? $configuration['method'] : 'GET';
        $options = isset($configuration['options']) ? $configuration['options'] : [];

        // Pretend to be a real browser.
        if (!isset($options['headers'])) {
            $options['headers'] = [];
        }
        if (!isset($options['headers']['user-agent'])) {
            $options['headers']['user-agent'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36';
        }

        $res = $client->request($method, $feedUrl, $options);
        if ($res->getStatusCode() !== 200) {
            return null;
        }

        $content = $res->getBody();
        // http://stackoverflow.com/questions/10290849/how-to-remove-multiple-utf-8-bom-sequences-before-doctype
        $bom = pack('H*', 'EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);

        $type = $this->feed->getType();
        switch ($type) {
            case 'json':
                $content = json_decode($content, true);
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
    private function processUrl($url)
    {
        return $url;
    }

  /**
   * @param array $data
   */
    public function createEvent(array $data)
    {
        if (isset($data['id'])) {
            $eventId = $data['id'];
            if (isset($this->feedEventIds[$eventId])) {
                $status = 'duplicated';
                $this->writeln(sprintf('%s (#%d): Event %s: %s', $this->feed->getName(), $this->feed->getId(), $status, $eventId));
                return;
            }
            $this->feedEventIds[$eventId] = $eventId;
        }
        $event = $this->eventImporter->import($data);
        if ($event) {
            $status = $event->getSkipImport() ? 'not changed' : ($event->getUpdatedAt() > $event->getCreatedAt() ? 'updated' : 'created');
            $this->writeln(sprintf('%s (#%d): Event %s: %s (%s)', $this->feed->getName(), $this->feed->getId(), $status, $event->getName(), $event->getFeedEventId()));
            $this->keepEvent($event);
        } else {
            $this->writeln(sprintf('%s (#%d): Cannot import event: %s', $this->feed->getName(), $this->feed->getId(), $data['id']));
        }
    }

  /**
   * @param $value
   * @param $name
   * @return \DateTime|null|string
   */
    public function convertValue($value, $name)
    {
        return $this->valueConverter->convert($value, $name);
    }

  /**
   * @param $messages
   * @param int $options
   */
    public function writeln($messages, $options = 0)
    {
        if ($this->output) {
            $this->output->writeln($messages, $options);
        }
    }
}
