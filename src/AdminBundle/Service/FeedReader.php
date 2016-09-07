<?php

namespace AdminBundle\Service;

use AdminBundle\Factory\EventFactory;
use AdminBundle\Factory\PlaceFactory;
use AdminBundle\Service\FeedReader\Controller;
use AdminBundle\Service\FeedReader\ValueConverter;
use AdminBundle\Entity\Feed;
use AppBundle\Entity\User;
use Gedmo\Blameable\BlameableListener;
use Symfony\Component\Console\Output\OutputInterface;

class FeedReader implements Controller {
  protected $eventFactory;
  protected $placeFactory;
  protected $valueConverter;
  protected $readers;
  protected $blameableListener;

  protected $feed;
  protected $output;

  public function __construct(EventFactory $eventFactory, PlaceFactory $placeFactory, ValueConverter $valueConverter, array $readers, BlameableListener $blameableListener) {
    $this->eventFactory = $eventFactory;
    $this->placeFactory = $placeFactory;
    $this->valueConverter = $valueConverter;
    $this->readers = $readers;
    $this->blameableListener = $blameableListener;
  }

  public function setOutput(OutputInterface $output) {
    $this->output = $output;
  }

  public function read($content, Feed $feed, User $user) {
    $this->feed = $feed;
    $this->eventFactory->setFeed($feed);

    if ($user) {
      // Tell Blameable which user is creating entities.
      if ($this->blameableListener) {
        $this->blameableListener->setUserValue($user);
      }
      $this->placeFactory->setUser($user);
    }

    // $imagesPath = $container->getParameter('admin.images_path');
    // $baseUrl = $container->getParameter('admin.base_url');

    list($reader, $content) = $this->getReader($feed, $content);
    $reader->read($content);
    $feed->setLastRead(new \DateTime());
  }

  private function getReader(Feed $feed, string $content) {
    $type = $feed->getType();

    if (!isset($this->readers[$type])) {
      throw new \Exception('Unknown feed type: ' . $type);
    }

    $reader = $this->readers[$type];
    $reader
      ->setController($this)
      ->setFeed($feed)
      // ->setUser($user)
      ;

    switch ($type) {
      case 'json':
        $content = json_decode($content, true);
        break;

      case 'xml':
        $content = new \SimpleXmlElement($content);
        break;
    }

    return array($reader, $content);
  }

  public function createEvent(array $data) {
    $data['feed'] = $this->feed;
    $event = $this->eventFactory->get($data);

    $status = ($event->getUpdatedAt() > $event->getCreatedAt()) ? 'updated' : 'created';
    $this->writeln(sprintf('% 8d %s: Event %s: %s (%s)', $this->feed->getId(), $this->feed->getName(), $status, $event->getName(), $event->getFeedEventId()));
  }

  public function convertValue($value, $name) {
    return $this->valueConverter->convert($value, $name);
  }

  protected function writeln($messages, $options = 0) {
    if ($this->output) {
      $this->output->writeln($messages, $options);
    }
  }
}
