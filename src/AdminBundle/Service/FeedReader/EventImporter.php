<?php

namespace AdminBundle\Service\FeedReader;

use AdminBundle\Entity\Feed;
use AdminBundle\Factory\EventFactory;
use AdminBundle\Factory\PlaceFactory;
use AdminBundle\Service\FileHandler;
use AppBundle\Entity\User;
use Psr\Log\LoggerInterface;

class EventImporter {
  protected $eventFactory;
  protected $placeFactory;
  protected $fileHandler;

  protected $feed;
  protected $user;
  protected $logger;

  public function __construct(EventFactory $eventFactory, PlaceFactory $placeFactory, FileHandler $fileHandler) {
    $this->eventFactory = $eventFactory;
    $this->placeFactory = $placeFactory;
    $this->fileHandler = $fileHandler;
  }

  public function setFeed(Feed $feed) {
    $this->feed = $feed;

    return $this;
  }

  public function setUser(User $user) {
    $this->user = $user;
    if ($this->placeFactory) {
      $this->placeFactory->setUser($user);
    }

    return $this;
  }

  public function setLogger(LoggerInterface $logger) {
    $this->logger = $logger;

    return $this;
  }

  public function import(array $data) {
    $event = $this->eventFactory->get($data);

    return $event;
  }
}