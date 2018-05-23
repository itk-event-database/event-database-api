<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service\FeedReader;

use AdminBundle\Entity\Feed;
use AdminBundle\Factory\EventFactory;
use AdminBundle\Factory\OrganizerFactory;
use AdminBundle\Factory\PlaceFactory;
use AdminBundle\Service\FileHandler;
use AppBundle\Entity\User;
use Psr\Log\LoggerInterface;

class EventImporter
{
    protected $eventFactory;
    protected $placeFactory;
    protected $organizerFactory;
    protected $fileHandler;

    protected $feed;
    protected $user;
    protected $logger;

    /**
     * @param \AdminBundle\Factory\EventFactory $eventFactory
     * @param \AdminBundle\Factory\PlaceFactory $placeFactory
     * @param \AdminBundle\Service\FileHandler  $fileHandler
     */
    public function __construct(EventFactory $eventFactory, PlaceFactory $placeFactory, OrganizerFactory $organizerFactory, FileHandler $fileHandler)
    {
        $this->eventFactory = $eventFactory;
        $this->placeFactory = $placeFactory;
        $this->organizerFactory = $organizerFactory;
        $this->fileHandler = $fileHandler;
    }

    /**
     * @param \AdminBundle\Entity\Feed $feed
     *
     * @return $this
     */
    public function setFeed(Feed $feed)
    {
        $this->feed = $feed;
        if ($this->eventFactory) {
            $this->eventFactory->setFeed($feed);
        }

        return $this;
    }

    /**
     * @param \AppBundle\Entity\User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        if ($this->placeFactory) {
            $this->placeFactory->setUser($user);
        }

        return $this;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return \AppBundle\Entity\Event|object
     */
    public function import(array $data)
    {
        $event = $this->eventFactory->get($data);

        return $event;
    }
}
