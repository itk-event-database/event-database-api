<?php

namespace AdminBundle\Command\FilesCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadFilesCommand extends FilesCommand {
  protected function configure() {
    $this
      ->setName('events:files:download')
      ->setDescription('Download files to local storage')
      ->addArgument('className', InputArgument::REQUIRED, 'The entity className to process')
      ->addArgument('ids', InputArgument::REQUIRED, 'The entity ids (csv)')
      ->addArgument('fields', InputArgument::REQUIRED, 'The entity fields to process (csv)');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $className = $input->getArgument('className');
    $ids = preg_split('/\s*,\s*/', $input->getArgument('ids'), NULL, PREG_SPLIT_NO_EMPTY);
    $fields = preg_split('/\s*,\s*/', $input->getArgument('fields'), NULL, PREG_SPLIT_NO_EMPTY);

    $service = $this->getContainer()->get('download_files');
    $service->setOutput($output);
    $service->process($className, $ids, $fields);
  }

}
