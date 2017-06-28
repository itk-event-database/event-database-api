<?php

namespace AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class CleanUpFilesCommand extends ContainerAwareCommand {
  /**
   * @var OutputInterface
   */
  private $output;

  protected function configure() {
    $this
      ->setName('events:files:cleanup')
      ->setDescription('Clean up files');
  }
  protected function execute(InputInterface $input, OutputInterface $output) {
    $filesToKeep = [];

    $fileManager = $this->getContainer()->get('file_handler');
    $filesUrl = $fileManager->getBaseUrl();

    $sql = 'select image from event where image like :image_pattern and deleted_at is null';
    $sql .= ' union select image from place where image like :image_pattern and deleted_at is null';
    $stmt = $this->getContainer()->get('doctrine')->getConnection()->prepare($sql);
    $stmt->execute([
      'image_pattern' => $filesUrl . '/%',
    ]);
    $imageFiles = array_filter(array_map(function ($row) use ($fileManager) { return $fileManager->getLocalPath($row['image']); }, $stmt->fetchAll()));

    $dir = new \DirectoryIterator($fileManager->getBaseDirectory());
    foreach ($dir as $fileinfo) {
      if ($fileinfo->isFile() && !in_array($fileinfo->getRealPath(), $imageFiles)) {
        echo $fileinfo->getRealPath(), PHP_EOL;
        unlink($fileinfo->getRealPath());
      }
    }

    //header('Content-type: text/plain'); echo var_export($imageFiles, true); die(__FILE__.':'.__LINE__.':'.__METHOD__);
  }

  protected function _execute(InputInterface $input, OutputInterface $output) {
    $this->output = $output;

    $fileManager = $this->getContainer()->get('file_handler');
    $filesUrl = $this->getContainer()->getParameter('admin.files_url');

    $filesToDelete = [];

    // Files from deleted events.
    $sql = 'select id, name, image from event where image like :image_pattern is not null and deleted_at is not null';
    $stmt = $this->getContainer()->get('doctrine')->getConnection()->prepare($sql);
    $stmt->execute([
      'image_pattern' => $filesUrl . '%',
    ]);
    while ($row = $stmt->fetch()) {
      $url = $row['image'];
      $filepath = $fileManager->getLocalPath($url);
      if ($filepath) {
        $filesToDelete[$filepath] = $filepath;
      }
    }

    // Files from deleted places.
    $sql = 'select id, name, image from place where image is not null and deleted_at is not null';
    $stmt = $this->getContainer()->get('doctrine')->getConnection()->prepare($sql);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
      $url = $row['image'];
      $filepath = $fileManager->getLocalPath($url);
      if ($filepath) {
        $filesToDelete[$filepath] = $filepath;
      }
    }

    // Exclude files from not deleted events.
    $sql = 'select id, name, image from event where image is not null and deleted_at is null';
    $stmt = $this->getContainer()->get('doctrine')->getConnection()->prepare($sql);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
      $url = $row['image'];
      $filepath = $fileManager->getLocalPath($url);
      if ($filepath) {
        unset($filesToDelete[$filepath]);
      }
    }

    // Exclude files from not deleted places.
    $sql = 'select id, name, image from place where image is not null and deleted_at is null';
    $stmt = $this->getContainer()->get('doctrine')->getConnection()->prepare($sql);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
      $url = $row['image'];
      $filepath = $fileManager->getLocalPath($url);
      if ($filepath) {
        unset($filesToDelete[$filepath]);
      }
    }

    foreach ($filesToDelete as $filepath => $_) {
      echo $filepath, PHP_EOL;
      unlink($filepath);
    }
  }

}
