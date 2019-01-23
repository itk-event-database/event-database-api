<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\EventListener;

use AppBundle\Entity\DailyOccurrence;
use AppBundle\Entity\Occurrence;
use AppBundle\Service\OccurrenceSplitterService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;

class OccurrenceListener
{
    private $occurrenceSplitter;

    /**
     * OccurrenceListener constructor.
     *
     * @param OccurrenceSplitterService $occurrenceSplitter
     */
    public function __construct(OccurrenceSplitterService $occurrenceSplitter)
    {
        $this->occurrenceSplitter = $occurrenceSplitter;
    }

    /**
     * {@inheritdoc}
     *
     * @param PreFlushEventArgs $args
     *
     * @throws \Exception
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getEntityManager();

        $this->insertDailyOccurrences($em);
        $this->deleteDailyOccurrences($em);
        $this->synchronizeDailyOccurrences($em);
    }

    /**
     * Insert new DailyOccurrences matching the Occurrences scheduled for insertion
     * in doctrines unit of work
     *
     * @param EntityManager $em
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function insertDailyOccurrences(EntityManager $em): void
    {
        $uow = $em->getUnitOfWork();
        $entities = $uow->getScheduledEntityInsertions();

        $occurrences = array_filter($entities, function ($entity) {
            return $entity instanceof Occurrence;
        });

        $classMetadata = $em->getClassMetadata(DailyOccurrence::class);

        foreach ($occurrences as $occurrence) {
            $dailyOccurrences = $this->occurrenceSplitter->getDailyOccurrences($occurrence);

            foreach ($dailyOccurrences as $dailyOccurrence) {
                $em->persist($dailyOccurrence);
                $uow->computeChangeSet($classMetadata, $dailyOccurrence);
            }
        }
    }

    /**
     * Synchronize the DailyOccurrences to match the Occurrences scheduled for updates
     * in doctrines unit of work
     *
     * @param EntityManager $em
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function synchronizeDailyOccurrences(EntityManager $em): void
    {
        $uow = $em->getUnitOfWork();
        $entities = $uow->getScheduledEntityUpdates();

        $occurrences = array_filter($entities, function ($entity) {
            return $entity instanceof Occurrence;
        });

        $classMetadata = $em->getClassMetadata(DailyOccurrence::class);

        foreach ($occurrences as $occurrence) {
            $newDailyOccurrences = $this->occurrenceSplitter->getDailyOccurrences($occurrence);
            $existingDailyOccurrences = $em->getRepository(DailyOccurrence::class)->findByOccurrence($occurrence);

            // Loop through new DailyOccurrences and copy their data to the first exiting DailyOccurrence to update
            // instead of doing delete/insert. Then remove the exiting DailyOccurrence.
            // Once there are no more exiting DailyOccurrences we persist new entities.
            $count = 0;
            $totalExisting = \count($existingDailyOccurrences);
            foreach ($newDailyOccurrences as $newDailyOccurrence) {
                if ($count < $totalExisting) {
                    $this->occurrenceSplitter->copyOccurrenceTraitPropertyValues($existingDailyOccurrences[$count], $newDailyOccurrence);
                    $uow->computeChangeSet($classMetadata, $existingDailyOccurrences[$count]);
                } else {
                    $em->persist($newDailyOccurrence);
                    $uow->computeChangeSet($classMetadata, $newDailyOccurrence);
                }
                $count++;
            }

            // If we still have exiting DailyOccurrence at this point they are redundant and should be deleted.
            while ($count < $totalExisting) {
                $em->remove($existingDailyOccurrences[$count]);
                $uow->computeChangeSet($classMetadata, $existingDailyOccurrences[$count]);
                $count++;
            }
        }
    }

    /**
     * Delete DailyOccurrences matching the Occurrences scheduled for deletion
     * in doctrines unit of work
     *
     * @param EntityManager $em
     */
    private function deleteDailyOccurrences(EntityManager $em): void
    {
        $uow = $em->getUnitOfWork();
        $entities = $uow->getScheduledEntityDeletions();

        $occurrences = array_filter($entities, function ($entity) {
            return $entity instanceof Occurrence;
        });

        if ($occurrences) {
            $qb = $em->createQueryBuilder();
            $qb->delete(DailyOccurrence::class, 'do');
            $qb->where('do.occurrence IN (:occurrences)');
            $qb->setParameter('occurrences', array_values($occurrences));

            $qb->getQuery()->execute();
        }
    }
}
