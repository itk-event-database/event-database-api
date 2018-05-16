<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Controller;

use AdminBundle\Entity\Feed;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Loggable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Loggable controller.
 *
 * @Route("/admin/loggable/{entityType}")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class LoggableController extends Controller
{
    /** @var EntityManagerInterface */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/{id}", name="admin_loggable_entity")
     *
     * @Method("GET")
     *
     * @Template("AdminBundle:Loggable:index.html.twig")
     *
     * @param mixed $entityType
     * @param mixed $id
     */
    public function indexAction($entityType, $id)
    {
        $className = $this->getClassName($entityType);
        $entity = $this->manager->getRepository($className)->find($id);

        if (null === $entity) {
            throw new NotFoundHttpException();
        }
        if (!$entity instanceof Loggable) {
            throw new BadRequestHttpException('Entity '.get_class($entity).' is not loggable');
        }
        $changes = $this->manager->getRepository(LogEntry::class)->getLogEntries($entity);

        return [
            'changes' => $changes,
        ];
    }

    private function getClassName($entityType)
    {
        switch ($entityType) {
            case 'Feed':
                return Feed::class;
        }

        throw new BadRequestHttpException('Invalid entity type: '.$entityType);
    }
}
