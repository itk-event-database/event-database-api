<?php

namespace AdminBundle\Command\LoggableCommand;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\LoggableListener;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Yaml\Yaml;

class InitLoggableCommand extends LoggableCommand
{
    /** @var EntityManagerInterface  */
    private $manager;

    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(EntityManagerInterface $manager, PropertyAccessorInterface $accessor)
    {
        parent::__construct();
        $this->manager = $manager;
        $this->accessor = $accessor;
    }

    protected function configure()
    {
        $this->setName('admin:loggable:init');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->verbose = true;

        $loggableRepository = $this->manager->getRepository(LogEntry::class);

        $listener = $this->getListener();
        $loggableClasses = $this->getLoggableClasses($listener);

        foreach ($loggableClasses as $class => $info) {
            $entities = $this->manager->getRepository($class)->findAll();
            foreach ($entities as $entity) {
                $id = $this->accessor->getValue($entity, 'id');
                $entries = $loggableRepository->getLogEntries($entity);
                if (count($entries) > 0) {
                    $this->info($class . '#'. $id . ' has changes; skipping.');
                    continue;
                }

                $data = [];
                foreach ($info['versioned'] as $property) {
                    $data[$property] = $this->accessor->getValue($entity, $property);
                }

                $logEntry = new LogEntry();
                $logEntry->setAction('create');
                $logEntry->setLoggedAt(new \DateTime());
                $logEntry->setObjectId($id);
                $logEntry->setObjectClass(get_class($entity));
                $logEntry->setVersion(1);
                $logEntry->setData($data);

                $this->info([$class . '#'. $id . ':', Yaml::dump($data)]);

                $this->manager->persist($logEntry);
                $this->manager->flush();
            }
        }
    }

    private function getLoggableClasses(LoggableListener $listener)
    {
        $classes = [];

        $metadata = $this->manager->getMetadataFactory()->getAllMetadata();

        foreach ($metadata as $metadatum) {
            $class = $metadatum->getName();
            $config = $listener->getConfiguration($this->manager, $class);
            if (isset($config['loggable'], $config['versioned'])) {
                $classes[$class] = $config;
            }
        }

        return $classes;
    }

    /**
     * @return LoggableListener
     */
    private function getListener()
    {
        foreach ($this->manager->getEventManager()->getListeners() as $event => $listeners) {
            foreach ($listeners as $hash => $listener) {
                if ($listener instanceof LoggableListener) {
                    return $listener;
                }
            }
        }

        throw new RuntimeException('Cannot find listener');
    }
}
