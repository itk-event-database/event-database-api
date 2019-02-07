<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Command\DailyOccurrences;

use AppBundle\Entity\DailyOccurrence;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('events:occurrences:generate-daily')
            ->setDescription('Generate and update all daily occurrences');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $occurrenceSplitter = $this->getContainer()->get('app.occurrence_splitter');

        $batchSize = 50;
        $i = 0;

        $count = $em->createQueryBuilder()
            ->select('count(o.id)')
            ->from('AppBundle:Occurrence', 'o')
            ->getQuery()
            ->getSingleScalarResult();

        $progressBar = new ProgressBar($output, $count);
        $progressBar->setRedrawFrequency($batchSize);
        $progressBar->setFormat('debug');
        $progressBar->start();

        $query = $em->createQueryBuilder()
            ->select('o')
            ->from('AppBundle:Occurrence', 'o')
            ->getQuery();
        $iterableResult = $query->iterate();

        foreach ($iterableResult as $row) {
            $occurrence = $row[0];

            $newDailyOccurrences = $occurrenceSplitter->createDailyOccurrenceCollection($occurrence);
            $existingDailyOccurrences = $em->getRepository(DailyOccurrence::class)->findByOccurrence($occurrence);

            // Loop through new DailyOccurrences and copy their data to the first exiting DailyOccurrence to update
            // instead of doing delete/insert. Then remove the exiting DailyOccurrence.
            // Once there are no more exiting DailyOccurrences we persist new entities.
            $count = 0;
            $totalExisting = \count($existingDailyOccurrences);
            foreach ($newDailyOccurrences as $newDailyOccurrence) {
                if ($count < $totalExisting) {
                    $occurrenceSplitter->copyOccurrenceTraitPropertyValues($existingDailyOccurrences[$count], $newDailyOccurrence);
                } else {
                    $em->persist($newDailyOccurrence);
                }
                ++$count;
            }

            // If we still have exiting DailyOccurrence at this point they are redundant and should be deleted.
            while ($count < $totalExisting) {
                $em->remove($existingDailyOccurrences[$count]);
                ++$count;
            }

            // Free memory when batch size is reached.
            if (0 === ($i % $batchSize)) {
                $em->flush();
                $em->clear();
            }

            $progressBar->advance();

            ++$i;
        }

        $progressBar->finish();
    }
}
