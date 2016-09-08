<?php

namespace AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReadFeedsCommand extends ContainerAwareCommand {
  protected function configure() {
    $this
      ->setName('events:feeds:read')
      ->setDescription('Read event feeds')
      ->addOption('name', null, InputOption::VALUE_REQUIRED, 'The name of the feed')
      ->addOption('id', null, InputOption::VALUE_REQUIRED, 'The ID of the feed');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $name = $input->getOption('name');
    $id = $input->getOption('id');

    $feeds = $this->getFeeds($id, $name);
    $noOfFeeds = count($feeds);

    if ($noOfFeeds == 0) {
      $output->writeln('No feeds found!');
    } else {
      $output->writeln(sprintf('Reading %s feed%s:', $noOfFeeds, ($noOfFeeds == 1) ? '' : 's'));
    }

    $reader = $this->getContainer()->get('feed_reader');
    $reader->setOutput($output);

    foreach ($feeds as $name => $feed) {
      $output->writeln([
        str_repeat('-', 80),
        'feed id: ' . $feed->getId(),
        'url:     ' . $feed->getUrl(),
        'user:    ' . $feed->getCreatedBy(),
        str_repeat('-', 80),
      ]);

      $reader->read($feed);
    }
  }

  private function getFeeds($id, $name) {
    $repository = $this->getContainer()->get('doctrine')->getRepository('AdminBundle:Feed');
    $query = [];
    if ($id) {
      $query['id'] = $id;
    }
    if ($name) {
      $query['name'] = $name;
    }

    return $repository->findBy($query);
  }
}
