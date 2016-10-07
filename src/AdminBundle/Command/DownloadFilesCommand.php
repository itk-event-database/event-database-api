<?php

namespace AdminBundle\Command;

use AppBundle\Entity\Entity;
use AppBundle\Entity\User;
use AppBundle\Job\DownloadFilesJob;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DownloadFilesCommand extends ContainerAwareCommand {
  /**
   * @var OutputInterface
   */
  private $output;

  /**
   * @var EntityManagerInterface
   */
  private $em;

  protected function configure() {
    $this
      ->setName('events:files:download')
      ->setDescription('Download files to local storage')
      ->addArgument('className', InputArgument::REQUIRED, 'The entity className to process')
      ->addArgument('ids', InputArgument::REQUIRED, 'The entity ids (csv)')
      ->addArgument('fields', InputArgument::REQUIRED, 'The entity fields to process (csv)');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->output = $output;

    $className = $input->getArgument('className');
    $ids = preg_split('/\s*,\s*/', $input->getArgument('ids'), null, PREG_SPLIT_NO_EMPTY);
    $fields = preg_split('/\s*,\s*/', $input->getArgument('fields'), null, PREG_SPLIT_NO_EMPTY);

    $service = $this->getContainer()->get('download_files');
    $service->setOutput($output);
    $service->process($className, $ids, $fields);
  }
}
