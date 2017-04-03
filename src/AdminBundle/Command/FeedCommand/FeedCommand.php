<?php

namespace AdminBundle\Command\FeedCommand;

use AdminBundle\Entity\Feed;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class FeedCommand extends ContainerAwareCommand {
  /**
   * @var OutputInterface
   */
  protected $output;

  protected function configure() {
    $this
      ->addOption('list', NULL, InputOption::VALUE_NONE, 'List all feeds');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->output = $output;

    if ($input->getOption('list')) {
      $this->listFeeds();
      exit;
    }
  }

  protected function listFeeds() {
    $feeds = $this->getFeeds(NULL, NULL, TRUE);
    foreach ($feeds as $feed) {
      $this->writeln(str_repeat('-', 80));
      $this->writeFeedInfo($feed);
      $this->writeln(str_repeat('-', 80));
    }
  }

  protected function writeFeedInfo(Feed $feed) {
    $this->writeln([
      'name:    ' . $feed->getName(),
      'id:      ' . $feed->getId(),
      'enabled: ' . ($feed->getEnabled() ? 'yes' : 'no'),
      'url:     ' . $feed->getUrl(),
      'user:    ' . $feed->getUser(),
    ]);
  }

  protected function writeln($messages) {
    $this->write($messages, TRUE);
  }

  protected function write($messages, $newline = FALSE) {
    if ($this->output) {
      $this->output->write($messages, $newline);
    }
  }

  /**
   * Get feeds.
   *
   */
  protected function getFeeds($ids, $names, $getAll = FALSE) {
    $repository = $this->getContainer()->get('doctrine')->getRepository('AdminBundle:Feed');
    $query = [];
    if ($ids) {
      $query['id'] = $ids;
    }
    if ($names) {
      $query['name'] = $names;
    }
    if (!$getAll) {
      $query['enabled'] = TRUE;
    }

    return $repository->findBy($query);
  }

}
