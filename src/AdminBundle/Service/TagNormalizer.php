<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service;

use AppBundle\Entity\Tag;
use AppBundle\Entity\UnknownTag;
use Doctrine\ORM\EntityManager;

class TagNormalizer implements TagNormalizerInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * TagNormalizer constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Normalize list of names to ensure they fit the DB schema.
     *
     * @param array $names
     *
     * @return array
     */
    public function normalize(array $names)
    {
        if (empty($names)) {
            return [];
        }

        $names = $this->trimLength($names);

        $names = $this->normalizeToDbName($names);

        return $names;
    }

    /**
     * Trim names in list to ensure they fit the DB schema.
     *
     * @param array $names
     *
     * @return array
     */
    private function trimLength(array $names): array
    {
        $metadata = $this->em->getClassMetadata(Tag::class);
        $maxNameLength = isset($metadata->fieldMappings, $metadata->fieldMappings['name'], $metadata->fieldMappings['name']['length'])
            ? (int) $metadata->fieldMappings['name']['length'] : 50;

        // Ensure we don't exceed field length in db
        return array_map(function ($name) use ($maxNameLength) {
            return mb_substr(trim($name), 0, $maxNameLength);
        }, $names);
    }

    /**
     * Normalize to the database tag name.
     *
     * The database collation is set so that 'cafe' and 'café' is
     * the same tag. This is the desired behavior however it breaks
     * comparisons on the PHP code level. Searching for all names in
     * the db and using the db name value solves this.
     *
     * @param array $names
     *
     * @return array
     */
    private function normalizeToDbName(array $names): array
    {
        $tagRepository = $this->em->getRepository(Tag::class);
        $unknownTagRepository = $this->em->getRepository(UnknownTag::class);

        $normalizedNames = [];
        foreach ($names as $name) {
            $tag = $tagRepository->findOneBy(['name' => $name]);
            if ($tag) {
                $normalizedNames[] = $tag->getName();
            } else {
                $unknownTag = $unknownTagRepository->findOneBy(['name' => $name]);
                if ($unknownTag) {
                    $normalizedNames[] = $unknownTag->getName();
                } else {
                    $normalizedNames[] = $name;
                }
            }
        }

        return $normalizedNames;
    }
}
