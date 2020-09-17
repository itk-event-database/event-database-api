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
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

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
        $metadata = $this->em->getClassMetadata(Tag::class);
        $maxNameLength = isset($metadata->fieldMappings, $metadata->fieldMappings['name'], $metadata->fieldMappings['name']['length'])
            ? (int) $metadata->fieldMappings['name']['length'] : 50;

        // Ensure we don't exceed field length in db
        $names = array_map(function ($name) use ($maxNameLength) {
            return mb_substr(trim($name), 0, $maxNameLength);
        }, $names);

        return $names;
    }
}
