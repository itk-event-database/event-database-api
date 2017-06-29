<?php

namespace AdminBundle\Command\FeedCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PreviewFeedCommand extends FeedCommand
{

    protected function configure()
    {
        parent::configure();
        $this
        ->setName('events:feed:preview')
        ->setDescription('Preview feed data')
        // ->addOption('raw', NULL, InputOption::VALUE_NONE, 'Dump raw, i.e. unprocessed, feed data')
        ->addOption('config', null, InputOption::VALUE_NONE, 'Dump feed configuration')
        ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Feed id to preview')
        ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Feed name to preview');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $feeds = $this->getFeeds($input->getOption('id'), $input->getOption('name'));
        if (count($feeds) !== 1) {
            throw new \Exception('Invalid feed specification.');
        }

        $feed = $feeds[0];

        if ($input->getOption('config')) {
            echo json_encode($feed->getConfiguration());
            exit;
        }

        $previewer = $this->getContainer()->get('feed_previewer');
        $previewer->read($feed);
        $events = $previewer->getEvents();

        echo json_encode($events);
    }
}
