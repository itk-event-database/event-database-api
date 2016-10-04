<?php

namespace AdminBundle\Command;

use AppBundle\Entity\Entity;
use AppBundle\Entity\User;
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
      ->addArgument('entity', InputArgument::REQUIRED, 'The entity (className:id) to process')
      ->addArgument('fields', InputArgument::REQUIRED, 'The entity fields to process (comma list)')
      ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->output = $output;
    $this->em = $this->getContainer()->get('doctrine')->getEntityManager();
    $accessor = new PropertyAccessor();

    $fileHandler = $this->getContainer()->get('file_handler');

    $spec = $input->getArgument('entity');
    $fields = preg_split('/\s*,\s*/', $input->getArgument('fields'), null, PREG_SPLIT_NO_EMPTY);
    $entities = $this->getEntities($spec);

    if ($entities) {
      foreach ($entities as $entity) {
        $this->authenticate($entity, $accessor);
        $this->writeln(get_class($entity) . '::' . $entity->getId());
        foreach ($fields as $field) {
          $value = $accessor->getValue($entity, $field);
          $newValue = $fileHandler->download($value);
          $this->write("\t" . $field . ': ');
          if ($newValue == $value) {
            $this->write("\t" . '(no change)');
          } else {
            $this->write("\t" . $value . ' → ' . $newValue);
            $accessor->setValue($entity, $field, $newValue);
          }
        }
        $this->writeln('');
        $this->em->persist($entity);
      }
      $this->em->flush();
    }
  }

  private function authenticate(Entity $entity, PropertyAccessor $accessor) {
    try {
      $user = $accessor->getValue($entity, 'created_by');
      if ($user instanceof User) {
        $token = new UsernamePasswordToken($user, NULL, 'main');
        $this->getContainer()->get('security.token_storage')->setToken($token);
      }
    } catch (\Exception $e) {}
  }

  private function writeln($messages) {
    $this->write($messages, true);
  }

  private function write($messages, $newline = false) {
    if ($this->output) {
      $this->output->write($messages, $newline);
    }
  }

  private function getEntities(string $spec) {
    $tokens = explode('::', $spec);
    $type = $tokens[0];
    $id = count($tokens) > 1 ? $tokens[1] : null;

    $repository = $this->em->getRepository($type);
    $entities = $id ? [$repository->find($id)] : $repository->findAll();

    return $entities;
  }
}
