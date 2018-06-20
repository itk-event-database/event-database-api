<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service;

use AppBundle\Entity\Entity;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class DownloadFilesService
{
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
     * @var array
     */
    private $configuration;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface      $entityManager
     * @param \AdminBundle\Service\FileHandler          $fileHandler
     * @param \AdminBundle\Service\AuthenticatorService $authenticator
     */
    public function __construct(EntityManagerInterface $entityManager, FileHandler $fileHandler, AuthenticatorService $authenticator, array $configuration)
    {
        $this->entityManager = $entityManager;
        $this->fileHandler = $fileHandler;
        $this->authenticator = $authenticator;
        $this->configuration = $configuration;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return $this
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @param string $className
     * @param $ids
     * @param array $fields
     */
    public function process(string $className, array $ids, array $fields)
    {
        $accessor = new PropertyAccessor();
        $entities = (1 === count($ids) && 'all' === $ids[0])
            ? $this->getEntitiesToProcess($className, $fields)
            : $this->entityManager->getRepository($className)->findBy(['id' => $ids]);

        $this->writeln(sprintf('#%s: %d', $className, count($entities)));
        if ($entities) {
            foreach ($entities as $index => $entity) {
                $this->authenticate($entity, $accessor);
                $this->writeln(sprintf('%04d/%04d %s::%s', $index + 1, count($entities), get_class($entity), $entity->getId()));
                foreach ($fields as $field) {
                    $value = $accessor->getValue($entity, $field);
                    if ($value) {
                        $newValue = $this->fileHandler->download($value);
                        $this->write("\t".$field.': ');
                        if (!$newValue) {
                            $status = $this->fileHandler->getErrorStatus();
                            $this->write("\t".'(not downloaded; code '.$status.')');
                            if (isset($this->configuration['fallback_image_url'])) {
                                $newValue = $this->fileHandler->resolve(
                                    $this->configuration['fallback_image_url'],
                                    ['status' => $status]
                                );
                            }
                        }
                        if ($newValue) {
                            if ($newValue === $value) {
                                $this->write("\t".'(no change)');
                            } else {
                                $this->write("\t".$value.' → '.$newValue);
                                $accessor->setValue($entity, $field, $newValue);
                                $originalValueField = 'original_'.$field;
                                if ($accessor->isWritable(
                                    $entity,
                                    $originalValueField
                                )) {
                                    $accessor->setValue(
                                        $entity,
                                        $originalValueField,
                                        $value
                                    );
                                }
                            }
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
     * Get a list of entities that have a non-local url in at least on of the specified fields.
     *
     * @param string $className
     * @param array  $fields
     *
     * @return mixed
     */
    private function getEntitiesToProcess(string $className, array $fields)
    {
        $baseUrl = (string) $this->fileHandler->getBaseUrl();
        // Make sure thae baseUrl end with a /.
        $baseUrl = rtrim($baseUrl, '/').'/';
        $qb = $this->entityManager->getRepository($className)->createQueryBuilder('e');
        foreach ($fields as $field) {
            $qb->orWhere('e.'.$field.' is not null and e.'.$field.' != \'\' and e.'.$field.' NOT LIKE :baseUrl');
        }
        $qb->setParameter('baseUrl', $baseUrl.'%');

        return $qb->getQuery()->execute();
    }

    /**
     * @param \AppBundle\Entity\Entity                           $entity
     * @param \Symfony\Component\PropertyAccess\PropertyAccessor $accessor
     */
    private function authenticate(Entity $entity, PropertyAccessor $accessor)
    {
        try {
            $user = $accessor->getValue($entity, 'created_by');
            if ($user instanceof User) {
                $this->authenticator->authenticate($user);
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * @param $messages
     */
    private function writeln($messages)
    {
        $this->write($messages, true);
    }

    /**
     * @param $messages
     * @param bool $newline
     */
    private function write($messages, $newline = false)
    {
        if ($this->output) {
            $this->output->write($messages, $newline);
        }
    }
}
