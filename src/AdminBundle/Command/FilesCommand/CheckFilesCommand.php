<?php

namespace AdminBundle\Command\FilesCommand;

use AppBundle\Entity\Event;
use AppBundle\Entity\Place;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckFilesCommand extends FilesCommand {
  protected function configure() {
    $this
      ->setName('events:files:check')
      ->setDescription('Check files');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    parent::execute($input, $output);
    $this->verbose = TRUE;

    $fileManager = $this->getContainer()->get('file_handler');
    $filesUrl = $fileManager->getBaseUrl();

    $client = new Client();

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
        $id = $row['id'];
        $url = $row['image'];
        try {
          $client->head($url);
          $this->info(sprintf('%s % 8d: %s %s', $className, $id, $url, 200));
        } catch (ClientException $exception) {
          $this->warning(sprintf('%s % 8d: %s %s', $className, $id, $url, $exception->getCode()));
        }
      }
    }
  }

}
