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
     * @param TagNormalizerInterface|null $tagNormalizer
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
     */
    public function loadOrCreateTags(array $names)
    {
        if ($this->tagNormalizer) {
            $names = $this->tagNormalizer->normalize($names, $this);
        }

        // Remove falsy values
        $names = array_filter($names);

        if ($this->unknownTagManager) {
            $this->createUnknownTags($names);
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

        $tags = $builder
            ->getQuery()
            ->getResult();

        return $tags;
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
     * Create all unknown tags.
     *
     * The EventDatabase has a concept og 'known' (i.e. official or approved) tags. If imported
     * events has tags that are not known they should be created as an 'UnknownTag'.
     *
     * @param array $names
     *   A normalized list of tag names
     *
     * @throws DBALException
     */
    private function createUnknownTags(array $names): void
    {
        $conn = $this->em->getConnection();
        $sql = 'SELECT EXISTS(SELECT * FROM tag WHERE name = :name) OR EXISTS(SELECT * FROM unknown_tag WHERE name = :name) as tagExists';
        $stmt = $conn->prepare($sql);

        $unknownTagNames = [];
        foreach ($names as $name) {
            $stmt->execute(array('name' => $name));
            $result = $stmt->fetch();

            if ($result['tagExists'] === '0') {
                $unknownTagNames[] = $name;
            }
        }

        if (!empty($unknownTagNames)) {
            $this->unknownTagManager->loadOrCreateTags($unknownTagNames);
        }
    }
}
