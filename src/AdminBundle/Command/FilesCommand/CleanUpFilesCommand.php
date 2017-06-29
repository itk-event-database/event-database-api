<?php

namespace AdminBundle\Command\FilesCommand;

use AppBundle\Entity\Event;
use AppBundle\Entity\Place;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanUpFilesCommand extends FilesCommand {
  protected function configure() {
    $this
      ->setName('events:files:cleanup')
      ->setDescription('Clean up downloaded files')
      ->addOption('dry-run', null, null, 'Show what will be done, but don\'t do anything.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    parent::execute($input, $output);
    $dryRun = $input->getOption('dry-run');
    $this->verbose = $this->verbose || $dryRun;

    $fileManager = $this->getContainer()->get('file_handler');
    $filesUrl = $fileManager->getBaseUrl();

    $usedFiles = [];
    // Get a list of all files referenced from non-deleted entities.
    $classNames = [Event::class, Place::class];
    foreach ($classNames as $className) {
      /** @var \Doctrine\DBAL\Query\QueryBuilder $queryBuilder */
      $queryBuilder = $this->getContainer()->get('doctrine')->getManager()->getRepository($className)->createQueryBuilder('e');
      $query = $queryBuilder->select(['e.id', 'e.image'])
        ->where($queryBuilder->expr()->like('e.image', ':image_pattern'))
        ->setParameter('image_pattern', $filesUrl . '/%')
        ->getQuery();
      $result = $query->execute();

      foreach ($result as $row) {
        $usedFiles[] = $fileManager->getLocalPath($row['image']);
      }
    }

    $this->info('Number of files in use:  ' . count($usedFiles));

    $deletedFiles = [];
    // Iterate over all files in the base directory and delete any file that is not used by a non-deleted entity.
    $dir = new \DirectoryIterator($fileManager->getBaseDirectory());
    foreach ($dir as $fileinfo) {
      if ($fileinfo->isFile() && !in_array($fileinfo->getRealPath(), $usedFiles)) {
        $path = $fileinfo->getRealPath();
        $this->info('Deleting file ' . $path);
        $deletedFiles[] = $path;
        if (!$dryRun) {
          unlink($path);
        }
      }
    }

    $this->info('Number of files deleted: ' . count($deletedFiles));
  }

}
