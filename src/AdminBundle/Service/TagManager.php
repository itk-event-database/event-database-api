<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service;

use AppBundle\Entity\CustomTaggable;
use AppBundle\Entity\UnknownTag;
use Doctrine\DBAL\DBALException;
use DoctrineExtensions\Taggable\Taggable;
use FPN\TagBundle\Entity\TagManager as BaseTagManager;

class TagManager extends BaseTagManager
{
    /** @var TagNormalizerInterface */
    private $tagNormalizer;

    /** @var TagManager */
    private $unknownTagManager;

    /**
     * @param null|TagNormalizerInterface $tagNormalizer
     */
    public function setTagNormalizer(TagNormalizerInterface $tagNormalizer = null)
    {
        $this->tagNormalizer = $tagNormalizer;
    }

    public function setUnknownTagManager(TagManager $tagManager = null)
    {
        $this->unknownTagManager = $tagManager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DBALException
     */
    public function loadOrCreateTags(array $names)
    {
        if (!$names) {
            return [];
        }

        if ($this->tagNormalizer) {
            $names = $this->tagNormalizer->normalize($names, $this);
        }

        // Remove falsy values
        $names = array_filter($names);

        // If we have an 'unknownTagManager' injected we need to handle unknown tags,
        // both the creation of unknown tags and 'translation' to a known tag.
        if ($this->unknownTagManager) {
            // Create unknown tags
            $unknownTags = $this->loadOrCreateUnknownTags($names);

            // Translate unknown tags to their know counter parts
            $validNames = $this->loadTagNames($names);
            $names = $this->addTranslatedTagNames($validNames, ...$unknownTags);
        }

        return parent::loadOrCreateTags($names);
    }

    public function setTags(array $tagsNames, Taggable $taggable)
    {
        $tags = $this->loadOrCreateTags($tagsNames);
        $this->replaceTags($tags, $taggable);

        // Store all tags as custom tags on object.
        if ($taggable instanceof CustomTaggable) {
            $customTags = array_diff($tagsNames, array_map(function ($tag) {
                return $tag->getName();
            }, $tags));
            if ($customTags) {
                $taggable->setCustomTags($customTags);
            }
        }
    }

    public function loadTags(array $names = null)
    {
        $builder = $this->em->createQueryBuilder();
        $builder
            ->select('t')
            ->from($this->tagClass, 't')
            ->orderBy('t.name');

        if ($names) {
            $builder->where($builder->expr()->in('t.name', $names));
        }

        return $builder->getQuery()->getResult();
    }

    public function createTag($name)
    {
        return parent::createTag($name);
    }

    public function deleteTag($tag)
    {
        // Delete relations to entities.
        $builder = $this->em->createQueryBuilder();
        $builder
            ->delete($this->taggingClass, 't')
            ->where($builder->expr()->eq('t.tag', $tag->getId()))
            ->getQuery()
            ->execute();

        // Delete tag.
        $this->em->remove($tag);
        $this->em->flush();

        return true;
    }

    /**
     * Load or create unknown tags from list of names.
     *
     * The EventDatabase has a concept og 'known' (i.e. official or approved) tags. If imported
     * events has tags that are not known they should be created as an 'UnknownTag'.
     *
     * @param array $names
     *   A normalized list of tag names
     *
     * @throws DBALException
     *
     * @return UnknownTag[]
     */
    private function loadOrCreateUnknownTags(array $names): array
    {
        $unknownTags = [];
        $unknownTagNames = [];

        $conn = $this->em->getConnection();
        $sql = 'SELECT EXISTS(SELECT * FROM tag WHERE name = :name) as tagExists';
        $stmt = $conn->prepare($sql);

        foreach ($names as $name) {
            $stmt->execute(['name' => $name]);
            $result = $stmt->fetch();

            if ('0' === $result['tagExists']) {
                $unknownTagNames[] = $name;
            }
        }

        if (!empty($unknownTagNames)) {
            $unknownTags = $this->unknownTagManager->loadOrCreateTags($unknownTagNames);
        }

        return $unknownTags;
    }

    /**
     * Add translated tag names to list of tag names.
     *
     * The EventDatabase has a concept og 'known' (i.e. official or approved) tags. Unknown tags
     * can be mapped to these tags. If an unknown tag has been mapped to a known tag we need to
     * add the name of the known tag to list of tag names.
     *
     * @param array $names
     *   The list of tag names to add to
     * @param UnknownTag ...$unknownTags
     *   The unknown tags to check and add
     *
     * @return array
     */
    private function addTranslatedTagNames(array &$names, UnknownTag ...$unknownTags): array
    {
        foreach ($unknownTags as $unknownTag) {
            $knownTag = $unknownTag->getTag();
            if ($knownTag) {
                $names[] = $knownTag->getName();
            }
        }

        return $names;
    }

    /**
     * Load list of valid tag names.
     *
     * @param array $names
     *
     * @return array
     */
    private function loadTagNames(array $names): array
    {
        $tags = $this->loadTags($names);

        return array_map(function ($tag) {
            return $tag->getName();
        }, $tags);
    }
}
