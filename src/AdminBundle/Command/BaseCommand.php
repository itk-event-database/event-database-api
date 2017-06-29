<?php

namespace AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends ContainerAwareCommand {
  /**
   * @var OutputInterface
   */
  protected $output;

  protected $verbose;

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->output = $output;
    $this->verbose = !!$input->getOption('verbose');
  }

  protected function writeln($messages) {
    $this->write($messages, TRUE);
  }

  protected function write($messages, $newline = FALSE) {
    if ($this->output) {
      $this->output->write($messages, $newline);
    }
  }

  protected function info($messages, $newline = TRUE) {
    if ($this->verbose) {
      if (!is_array($messages)) {
        $messages = [$messages];
      }
      $messages = array_map(function ($message) {
        return '<info>' . $message . '</info>';
      }, $messages);
      $this->write($messages, $newline);
    }
  }

  protected function warning($messages, $newline = TRUE) {
    if ($this->verbose) {
      if (!is_array($messages)) {
        $messages = [$messages];
      }
      $messages = array_map(function ($message) {
        return '<error>' . $message . '</error>';
      }, $messages);
      $this->write($messages, $newline);
    }
  }

}
