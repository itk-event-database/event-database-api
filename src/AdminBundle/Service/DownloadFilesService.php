<?php

namespace AdminBundle\Service;

use AppBundle\Entity\Entity;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 *
 */
class DownloadFilesService {
  /**
   * @var EntityManagerInterface
   */
  private $entityManager;

  /**
   * @var FileHandler
   */
  private $fileHandler;

  /**
   * @var AuthenticatorService
   */
  private $authenticator;

  /**
   * @var OutputInterface
   */
  private $output;

  /**
   * @param \Doctrine\ORM\EntityManagerInterface $entityManager
   * @param \AdminBundle\Service\FileHandler $fileHandler
   * @param \AdminBundle\Service\AuthenticatorService $authenticator
   */
  public function __construct(EntityManagerInterface $entityManager, FileHandler $fileHandler, AuthenticatorService $authenticator) {
    $this->entityManager = $entityManager;
    $this->fileHandler = $fileHandler;
    $this->authenticator = $authenticator;
  }

  /**
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   * @return $this
   */
  public function setOutput(OutputInterface $output) {
    $this->output = $output;

    return $this;
  }

  /**
   * @param string $className
   * @param $id
   * @param array $fields
   */
  public function process(string $className, $id, array $fields) {
    $accessor = new PropertyAccessor();

    $entities = $this->entityManager->getRepository($className)->findBy(['id' => $id]);

    if ($entities) {
      foreach ($entities as $entity) {
        $this->authenticate($entity, $accessor);
        $this->writeln(get_class($entity) . '::' . $entity->getId());
        foreach ($fields as $field) {
          $value = $accessor->getValue($entity, $field);
          if ($value) {
            $newValue = $this->fileHandler->download($value);
            $this->write("\t" . $field . ': ');
            if (!$newValue) {
              $this->write("\t" . '(not downloaded)');
            }
            elseif ($newValue == $value) {
              $this->write("\t" . '(no change)');
            }
            else {
              $this->write("\t" . $value . ' → ' . $newValue);
              $accessor->setValue($entity, $field, $newValue);
            }
          }
        }
        $this->writeln('');
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
      }
    }
  }

  /**
   * @param \AppBundle\Entity\Entity $entity
   * @param \Symfony\Component\PropertyAccess\PropertyAccessor $accessor
   */
  private function authenticate(Entity $entity, PropertyAccessor $accessor) {
    try {
      $user = $accessor->getValue($entity, 'created_by');
      if ($user instanceof User) {
        $this->authenticator->authenticate($user);
      }
    }
    catch (\Exception $e) {
    }
  }

  /**
   * @param $messages
   */
  private function writeln($messages) {
    $this->write($messages, TRUE);
  }

  /**
   * @param $messages
   * @param bool $newline
   */
  private function write($messages, $newline = FALSE) {
    if ($this->output) {
      $this->output->write($messages, $newline);
    }
  }

}
