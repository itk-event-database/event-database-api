<?php

namespace AppBundle\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use SimpleXMLElement;

class CleanUpCommand extends ContainerAwareCommand {
  protected function configure() {
    $this->setName('events:cleanup')
      ->setDescription('Clean up stuff');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $em = $this->getContainer()->get('doctrine')->getEntityManager('default');

    $output->writeln('Removing orphaned Occurrences …');
    $query = $em->createQuery('SELECT o FROM AppBundle:Occurrence o WHERE o.event is null');

    $result = $query->getResult();
    foreach ($result as $entity) {
      $em->remove($entity);
    }
    $em->flush();
    $output->writeln('Done.');
    $output->writeln(sprintf('%d removed', count($result)));
  }
}