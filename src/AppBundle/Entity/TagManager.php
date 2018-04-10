<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Entity;

use AdminBundle\Service\TagNormalizerInterface;
use DoctrineExtensions\Taggable\Taggable;
use FPN\TagBundle\Entity\TagManager as BaseTagManager;

class TagManager extends BaseTagManager
{
    /**
     * @var TagNormalizerInterface
     */
    private $tagNormalizer;

    public function setTagNormalizer(TagNormalizerInterface $tagNormalizer = null)
    {
        $this->tagNormalizer = $tagNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function loadOrCreateTags(array $names)
    {
        if ($this->tagNormalizer) {
            $names = $this->tagNormalizer->normalize($names, $this);
        }
        $names = array_filter($names);

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
}
