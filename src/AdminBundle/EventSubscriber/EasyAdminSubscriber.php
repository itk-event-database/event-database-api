<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\EventSubscriber;

use AdminBundle\Service\TagManager;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private $tagManager;
    private $container;

    public function __construct(TagManager $tagManager, ContainerInterface $container)
    {
        $this->tagManager = $tagManager;
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::POST_INITIALIZE => ['addQueryParameters'],
        ];
    }

    public function addQueryParameters(GenericEvent $event)
    {
        $request = $event->getArgument('request');
        if ($request) {
            $action = $request->get('action', 'list');
            $entity = $event->getArgument('entity');
            if (isset($entity[$action]['params'])) {
                foreach ($entity[$action]['params'] as $name => $value) {
                    if (!$request->query->has($name)) {
                        $request->query->add([$name => $value]);
                    }
                }
            }
        }
    }
}
