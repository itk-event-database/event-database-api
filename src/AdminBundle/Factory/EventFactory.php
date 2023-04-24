<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Factory;

use AdminBundle\Entity\Feed;
use AppBundle\Entity\Entity;
use AppBundle\Entity\Event;
use Doctrine\Common\Collections\ArrayCollection;

class EventFactory extends EntityFactory
{
    /**
     * @var Feed
     */
    protected $feed;

    /**
     * @var OrganizerFactory
     */
    protected $organizerFactory;

    /**
     * @var OccurrenceFactory
     */
    protected $occurrenceFactory;

    /**
     * @param \AdminBundle\Entity\Feed $feed
     */
    public function setFeed(Feed $feed)
    {
        $this->feed = $feed;
        if ($this->valueConverter) {
            $this->valueConverter->setFeed($feed);
        }
    }

    public function setOrganizerFactory(OrganizerFactory $organizerFactory)
    {
        $this->organizerFactory = $organizerFactory;
    }

    /**
     * @param \AdminBundle\Factory\OccurrenceFactory $occurrenceFactory
     */
    public function setOccurrenceFactory(OccurrenceFactory $occurrenceFactory)
    {
        $this->occurrenceFactory = $occurrenceFactory;
    }

    /**
     * @param array $data
     *
     * @return \AppBundle\Entity\Event|object
     */
    public function get(array $data)
    {
        $entity = $this->getEntity($data);
        if ($entity && !$entity->getSkipImport()) {
            $this->setValues($entity, $data);
            $this->persist($entity);
            $this->flush();
        }

        return $entity;
    }

    /**
     * @param \AppBundle\Entity\Entity $entity
     * @param $key
     * @param $value
     */
    protected function setValue(Entity $entity, $key, $value)
    {
        if ($entity instanceof Event) {
            if ($this->accessor->isWritable($entity, $key)) {
                switch ($key) {
                    case 'occurrences':
                        $occurrences = new ArrayCollection();
                        foreach ($value as $item) {
                            $item['event'] = $entity;
                            $occurrence = $this->occurrenceFactory->get($item);
                            $occurrences->add($occurrence);
                        }
                        $entity->setOccurrences($occurrences);

                        return;
                    case 'organizer':
                        $organizer = $this->organizerFactory->get($value);
                        $entity->setOrganizer($organizer);

                        return;
                    case 'partnerOrganizers':
                        $partnerOrganizers = array_map([$this->organizerFactory, 'get'], $value);
                        $entity->setPartnerOrganizers($partnerOrganizers);

                        return;
                }
            }
        }

        parent::setValue($entity, $key, $value);
    }

    /**
     * @param array $data
     *
     * @return \AppBundle\Entity\Event|object
     */
    private function getEntity(array $data)
    {
        $feedEventId = isset($data['id']) ? $data['id'] : null;

        if (!$this->feed || !$feedEventId) {
            return null;
        }

        // An event may have been (soft) deleted. We want to reuse it.
        $hasSoftdeleteable = false;
        $filters = $this->em->getFilters();
        if ($filters->has('softdeleteable')) {
            $hasSoftdeleteable = true;
            $filters->disable('softdeleteable');
        }

        $event = $this->em->getRepository('AppBundle:Event')->findOneBy([
        'feed' => $this->feed,
        'feedEventId' => $feedEventId,
        ]);

        if ($hasSoftdeleteable) {
            $filters->enable('softdeleteable');
        }

        if (null === $event) {
            $event = new Event();
            $event->setFeed($this->feed);
            $event->setFeedEventId($feedEventId);
        }

        $hash = $this->getEventHash($data);

        // Skip importing the event, if it has not changed and has not been deleted.
        if ($hash === $event->getFeedEventHash() && null === $event->getDeletedAt()) {
            $event->setSkipImport(true);
        }

        $event->setFeedEventHash($hash)
        ->setDeletedAt(null);

        return $event;
    }

    private function getEventHash(array $data)
    {
        return md5(json_encode($data));
    }
}
