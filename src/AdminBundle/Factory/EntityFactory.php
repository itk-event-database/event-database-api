<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Factory;

use AdminBundle\Service\FeedReader\ValueConverter;
use AppBundle\Entity\Entity;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineExtensions\Taggable\Taggable;
use FPN\TagBundle\Entity\TagManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class EntityFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var ValueConverter
     */
    protected $valueConverter;

    /**
     * @var TagManager
     */
    protected $tagManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    protected $accessor;

    protected $user;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \Doctrine\ORM\EntityManagerInterface                      $em
     * @param \AdminBundle\Service\FeedReader\ValueConverter            $valueConverter
     * @param \FPN\TagBundle\Entity\TagManager                          $tagManager
     */
    public function __construct(ContainerInterface $container, EntityManagerInterface $em, ValueConverter $valueConverter, TagManager $tagManager = null)
    {
        $this->container = $container;
        $this->em = $em;
        $this->valueConverter = $valueConverter;
        $this->tagManager = $tagManager;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param \AppBundle\Entity\User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param $entity
     */
    protected function persist($entity)
    {
        $this->em->persist($entity);
    }

    protected function flush()
    {
        $this->em->flush();
    }

    /**
     * @param \AppBundle\Entity\Entity $entity
     * @param array                    $values
     *
     * @return $this
     */
    protected function setValues(Entity $entity, array $values)
    {
        $metadata = $this->em->getClassMetadata(get_class($entity));
        foreach ($values as $key => $value) {
            if ($this->valueConverter) {
                $value = $this->valueConverter->convert($value, $key);
            }

            // Normalize value.
            if (isset($metadata->fieldMappings[$key])) {
                $mapping = $metadata->fieldMappings[$key];
                if ('string' === $mapping['type']) {
                    // Truncate string value.
                    $maxLength = $mapping['length'] ?: 255;
                    $value = mb_substr($value, 0, $maxLength);
                }
            }

            $this->setValue($entity, $key, $value);
        }

        return $this;
    }

    /**
     * @param \AppBundle\Entity\Entity $entity
     * @param $key
     * @param $value
     */
    protected function setValue(Entity $entity, $key, $value)
    {
        switch ($key) {
            case 'id':
                return;
            case 'tags':
                if ($entity instanceof Taggable && $this->tagManager) {
                    $tags = $this->tagManager->setTags($value, $entity);
                }

                return;
        }

        if ($this->accessor->isWritable($entity, $key)) {
            $this->accessor->setValue($entity, $key, $value);
        }
    }

    protected function getUser()
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->container->has('security.token_storage')) {
            $token = $this->container->get('security.token_storage')->getToken();

            return $token ? $token->getUser() : null;
        }

        return null;
    }
}
