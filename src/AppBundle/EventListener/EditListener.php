<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\EventListener;

use AppBundle\Security\Authorization\Voter\EditVoter;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Gedmo\Blameable\Blameable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EditListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if ($object instanceof Blameable) {
            if (!$this->isGranted(EditVoter::UPDATE, $object)) {
                throw new AccessDeniedHttpException('Access denied');
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if ($object instanceof Blameable) {
            if (!$this->isGranted(EditVoter::REMOVE, $object)) {
                throw new AccessDeniedHttpException('Access denied');
            }
        }
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied object.
     *
     * @param mixed $attributes
     *                          The attributes
     * @param mixed $object
     *                          The object
     *
     * @throws \LogicException
     *
     * @return bool
     */
    protected function isGranted($attributes, $object = null)
    {
        if (!$this->container->has('security.authorization_checker')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        return $this->container->get('security.authorization_checker')->isGranted($attributes, $object);
    }
}
