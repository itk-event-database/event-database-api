<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\EventSubscriber;

use AppBundle\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Security;

/**
 * Class EventAccessSubscriber
 */
class EventAccessSubscriber implements EventSubscriberInterface
{
    /** @var Security */
    private $security;

    /**
     * EventAccessSubscriber constructor.
     *
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EasyAdminEvents::PRE_PERSIST => ['setEventAccessFromUser'],
        ];
    }

    /**
     * Set access for event depending on logged in users role
     *
     * @param GenericEvent $event
     */
    public final function setEventAccessFromUser(GenericEvent $event): void
    {
        $entity = $event->getSubject();

        if (!($entity instanceof Event)) {
            return;
        }

        if (!$this->security->isGranted('ROLE_FULL_ACCESS_EVENT_EDITOR')) {
            $entity->setHasFullAccess(false);
        } else {
            $entity->setHasFullAccess(true);
        }
    }
}
