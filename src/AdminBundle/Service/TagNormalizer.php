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
use Symfony\Component\DependencyInjection\ContainerInterface;

class TagNormalizer implements TagNormalizerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param array                                                     $configuration
     */
    public function __construct(ContainerInterface $container, array $configuration)
    {
        $this->container = $container;
        $this->configuration = $configuration;
    }

    /**
     * @param array $names
     *
     * @return array
     */
    public function normalize(array $names)
    {
        if (empty($names)) {
            return [];
        }
        $em = $this->container->get('doctrine')->getManager();
        $metadata = $em->getClassMetadata(Tag::class);
        $maxNameLength = isset($metadata->fieldMappings, $metadata->fieldMappings['name'], $metadata->fieldMappings['name']['length'])
                ? (int) $metadata->fieldMappings['name']['length'] : 50;
        $names = array_map(function ($name) use ($maxNameLength) {
            return mb_substr(trim($name), 0, $maxNameLength);
        }, $names);
        $tagManager = $this->getTagManager();
        $tags = $tagManager->loadTags($names);

        $validNames = array_map(function ($tag) {
            return $tag->getName();
        }, $tags);

        $unknownNames = array_udiff($names, $validNames, 'strcasecmp');
        if ($unknownNames) {
            $unknownTags = $this->getUnknownTagManager()->loadOrCreateTags($unknownNames);
            foreach ($unknownTags as $unknownTag) {
                $tag = $unknownTag->getTag();
                if ($tag) {
                    $validNames[] = $tag->getName();
                }
            }
        }

        return array_unique($validNames);
    }

    /**
     * @return TagManager
     */
    private function getTagManager()
    {
        return $this->container->get($this->configuration['services']['tag_manager']);
    }

    /**
     * @return TagManager
     */
    private function getUnknownTagManager()
    {
        return $this->container->get($this->configuration['services']['unknown_tag_manager']);
    }
}
