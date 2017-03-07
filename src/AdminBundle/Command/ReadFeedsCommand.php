<?php

namespace AdminBundle\Command;

use AdminBundle\Entity\Feed;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReadFeedsCommand extends ContainerAwareCommand {
  /**
   * @var OutputInterface
   */
  private $output;

  protected function configure() {
    $this
        ->setName('events:feeds:read')
        ->setDescription('Read event feeds')
        ->addOption('name', NULL, InputOption::VALUE_REQUIRED, 'The name of the feed')
        ->addOption('id', NULL, InputOption::VALUE_REQUIRED, 'The ID of the feed')
        ->addOption('list', NULL, InputOption::VALUE_NONE, 'List all feeds');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->output = $output;

    if ($input->getOption('list')) {
      $this->listFeeds();
      return;
    }

    $name = $input->getOption('name');
    $id = $input->getOption('id');

    $feeds = $this->getFeeds($id, $name);
    $noOfFeeds = count($feeds);

    if ($noOfFeeds == 0) {
      $this->writeln('No feeds found!');
    }
    else {
      $this->writeln(sprintf('Reading %s feed%s:', $noOfFeeds, ($noOfFeeds == 1) ? '' : 's'));
    }

    $reader = $this->getContainer()->get('feed_reader');
    $reader->setOutput($output);

    foreach ($feeds as $feed) {
      $this->writeln(str_repeat('-', 80));
      $this->writeFeedInfo($feed);
      $this->writeln(str_repeat('-', 80));

      try {
        $reader->read($feed);
      } catch (\Throwable $t) {
        $this->writeln('-- Error -----------------------------------------------------------------------');
        $this->writeln(sprintf('%s (feed #%d)', $t->getMessage(), $feed->getId()));
        $this->writeln('--------------------------------------------------------------------------------');
      }
    }
  }

  private function listFeeds() {
    $feeds = $this->getFeeds(NULL, NULL);
    foreach ($feeds as $feed) {
      $this->writeln(str_repeat('-', 80));
      $this->writeFeedInfo($feed);
      $this->writeln(str_repeat('-', 80));
    }
  }

  private function writeFeedInfo(Feed $feed) {
    $this->writeln([
      'name:   ' . $feed->getName(),
      'id:     ' . $feed->getId(),
      'url:    ' . $feed->getUrl(),
      'user:   ' . $feed->getCreatedBy(),
    ]);
  }

  private function writeln($messages) {
    $this->write($messages, TRUE);
  }

  private function write($messages, $newline = FALSE) {
    if ($this->output) {
      $this->output->write($messages, $newline);
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
