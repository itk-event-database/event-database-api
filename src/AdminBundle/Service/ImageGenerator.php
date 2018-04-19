<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\Console\Output\OutputInterface;

class ImageGenerator
{
    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $entityManager;

    /** @var \AdminBundle\Service\FileHandler */
    private $fileHandler;

    /** @var \Liip\ImagineBundle\Imagine\Filter\FilterManager */
    private $filterManager;

    /** @var \Liip\ImagineBundle\Imagine\Cache\CacheManager */
    private $cacheManager;

    /** @var \Liip\ImagineBundle\Imagine\Data\DataManager */
    private $dataManager;

    public function __construct(EntityManagerInterface $entityManager, FileHandler $fileHandler, FilterManager $filterManager, CacheManager $cacheManager, DataManager $dataManager)
    {
        $this->entityManager = $entityManager;
        $this->fileHandler = $fileHandler;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
        $this->dataManager = $dataManager;
    }

    public function generate(string $className, array $entityIds = null, OutputInterface $output, $imageFieldName = 'image', $imagesFieldName = 'images', $idFieldName = 'id')
    {
        $generate = false;
        $metadata = $this->entityManager->getClassMetadata($className);
        $idField = $metadata->getFieldName($idFieldName);
        $imagesField = $metadata->getFieldName($imagesFieldName);

        $sql = 'update '.$metadata->getTableName().' set '.$imagesField.' = :images where '.$idField.' = :id';
        $updateStmt = $this->entityManager->getConnection()->prepare($sql);

        $filters = array_keys($this->filterManager->getFilterConfiguration()->all());

        $entities = $this->getEntities($className, $entityIds, $imageFieldName, $imagesFieldName, $idFieldName);

        foreach ($entities as $index => $entity) {
            $url = $entity['image'];
            $path = $this->fileHandler->getLocalUrl($url);
            if (null !== $output) {
                $output->writeln(sprintf('% 8d % 8d %s %s', $index, $entity['id'], $url, $path));
            }

            if (null !== $path) {
                $images = [];
                foreach ($filters as $filter) {
                    if ($generate) {
                        if (!$this->cacheManager->isStored($path, $filter)) {
                            try {
                                $binary = $this->dataManager->find(
                                    $filter,
                                    $path
                                );
                                $binary = $this->filterManager->applyFilter(
                                    $binary,
                                    $filter
                                );
                                $this->cacheManager->store(
                                    $binary,
                                    $path,
                                    $filter
                                );
                            } catch (\Exception $ex) {
                                if (null !== $output) {
                                    $output->writeln(
                                        '<error>'.$ex->getMessage().'</error>'
                                    );
                                }

                                break;
                            }
                        }
                    }
                    $images[$filter] = $this->cacheManager->getBrowserPath($path, $filter);
                }

                switch ($metadata->getTypeOfField($imagesFieldName)) {
                    case 'json_array':
                        $encodedImages = json_encode($images);

                        break;
                    case 'array':
                        $encodedImages = serialize($images);

                        break;
                }
                $updateStmt->execute(['images' => $encodedImages, 'id' => $entity['id']]);
            }
        }
    }

    public function reset(string $className, OutputInterface $output, $imageFieldName = 'image', $imagesFieldName = 'images')
    {
        $metadata = $this->entityManager->getClassMetadata($className);
        $imagesField = $metadata->getFieldName($imagesFieldName);

        $sql = 'update '.$metadata->getTableName().' set '.$imagesField.' = null';
        $updateStmt = $this->entityManager->getConnection()->prepare($sql);
        $updateStmt->execute();
    }

    private function getEntities($className, $entityIds, $imageFieldName = 'image', $imagesFieldName = 'images', $idFieldName = 'id')
    {
        $metadata = $this->entityManager->getClassMetadata($className);
        $queryBuilder = $this->entityManager->getRepository($className)->createQueryBuilder('e');
        $idField = 'e.'.$metadata->getFieldName($idFieldName);
        $imageField = 'e.'.$metadata->getFieldName($imageFieldName);
        $imagesField = 'e.'.$metadata->getFieldName($imagesFieldName);
        $queryBuilder->select([$idField.' as id', $imageField.' as image', $imagesField.' as images'])
            ->where($queryBuilder->expr()->isNotNull($imageField))
            ->andWhere($imageField.' != :empty')
            ->setParameter('empty', '')
            ->andWhere($queryBuilder->expr()->isNull($imagesField))
            ->setMaxResults(1000);

        if (null !== $entityIds) {
            $queryBuilder->andWhere($queryBuilder->expr()->in($idField, $entityIds));
        }

        $query = $queryBuilder->getQuery();

        return $query->execute();
    }
}
