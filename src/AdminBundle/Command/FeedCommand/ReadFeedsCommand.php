<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Command\FeedCommand;

use AdminBundle\Entity\Feed;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReadFeedsCommand extends FeedCommand
{
    protected function configure()
    {
        parent::configure();
        $this
        ->setName('events:feeds:read')
        ->setDescription('Read event feeds')
        ->addOption('name', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'The name of the feed')
        ->addOption('id', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'The id of the feed');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $name = $input->getOption('name');
        $id = $input->getOption('id');

        $feeds = $this->getFeeds($id, $name);
        $noOfFeeds = count($feeds);

        if (0 === $noOfFeeds) {
            $this->writeln('No feeds found!');
        } else {
            $this->writeln(sprintf('Reading %s feed%s:', $noOfFeeds, (1 === $noOfFeeds) ? '' : 's'));
        }

        $reader = $this->getContainer()->get('feed_reader');
        $reader->setOutput($output);

        foreach ($feeds as $feed) {
            $this->writeln(str_repeat('-', 80));
            $this->writeFeedInfo($feed);
            $this->writeln(str_repeat('-', 80));

            try {
                $reader->read($feed, null, true);
                $em = $this->getContainer()->get('doctrine.orm.entity_manager');
                $feed->setLastRead(new \DateTime());
                $em->persist($feed);
                $em->flush();
            } catch (\Throwable $t) {
                $this->writeln('-- Error -----------------------------------------------------------------------');
                $this->writeln(sprintf('%s (feed #%d)', $t->getMessage(), $feed->getId()));
                if ($input->getOption('verbose')) {
                    $this->writeln($t->getTraceAsString());
                }
                $this->writeln('--------------------------------------------------------------------------------');
            }
        }
    }
}
