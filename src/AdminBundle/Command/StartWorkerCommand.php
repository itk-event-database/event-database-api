<?php

namespace AdminBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use ResqueBundle\Resque\Command\StartWorkerCommand as BaseStartWorkerCommand;
use Symfony\Component\Validator\Constraints\IsFalse;

/**
 * Class StartWorkerCommand
 *
 * A wrapper with exclusive logging around "resque:worker-start" (which see).
 *
 * @package AdminBundle\Command
 */
class StartWorkerCommand extends BaseStartWorkerCommand {
  /**
   *
   */
  protected function configure() {
    parent::configure();
    $this->setName('events:resque:worker-start')
      ->addOption('talkative', NULL, InputOption::VALUE_NONE);
  }

  /**
   * @param InputInterface $input
   * @param OutputInterface $output
   * @return int|null|void
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    // @see http://stackoverflow.com/a/20525207
    $lockPath = '/tmp/' . preg_replace('/[^a-z_]/i', '-', __CLASS__) . '.lock';
    $handle = fopen($lockPath, 'w');
    if (flock($handle, LOCK_EX | LOCK_NB)) {
      $input->setOption('foreground', true);
      $input->setOption('quiet', true);
      parent::execute($input, $output);
      flock($handle, LOCK_UN);
    } else {
      if ($input->getOption('talkative')) {
        $output->writeln('Already running');
      }
    }
    fclose($handle);
  }
}
