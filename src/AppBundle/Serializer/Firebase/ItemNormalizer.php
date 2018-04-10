<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Serializer\Firebase;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;
use AppBundle\Entity\Place;
use AppBundle\Entity\Tag;
use AppBundle\Entity\TagManager;
use DoctrineExtensions\Taggable\Taggable;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Class AoAItemNormalizer.
 */
class ItemNormalizer extends AbstractItemNormalizer
{
    const FORMAT = 'firebase';

    /**
     * @var TagManager
     */
    private $tagManager;

    public function __construct(PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory, PropertyMetadataFactoryInterface $propertyMetadataFactory, IriConverterInterface $iriConverter, ResourceClassResolverInterface $resourceClassResolver, PropertyAccessorInterface $propertyAccessor = null, NameConverterInterface $nameConverter = null, TagManager $tagManager)
    {
        parent::__construct($propertyNameCollectionFactory, $propertyMetadataFactory, $iriConverter, $resourceClassResolver, $propertyAccessor, $nameConverter);
        $this->tagManager = $tagManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return self::FORMAT === $format && parent::supportsNormalization($data, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($object instanceof Event) {
            return $this->normalizeEvent($object);
        } elseif ($object instanceof Occurrence) {
            return $this->normalizeOccurrence($object);
        } elseif ($object instanceof Place) {
            return $this->normalizePlace($object);
        } elseif ($object instanceof Tag) {
            return $this->normalizeTag($object);
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return false;
    }

    protected function getAttributeValue($object, $attribute, $format = null, array $context = [])
    {
        if ($object instanceof Taggable && 'tags' === $attribute) {
            $this->tagManager->loadTagging($object);

            return $object->getTags()->map(function ($tag) {
                return $tag->getName();
            });
        }

        return parent::getAttributeValue($object, $attribute, $format, $context);
    }

    private function normalizeEvent(Event $event)
    {
        $data = [];

        $data['id'] = $event->getId();
        $data['occurrences'] = $event->getOccurrences() ? $event->getOccurrences()->map(function ($item) {
            return $item->getId();
        })->toArray() : null;
        $data['ticketPurchaseUrl'] = $event->getTicketPurchaseUrl();
        $data['excerpt'] = $event->getExcerpt();
        $data['tags'] = $event->getTags() ? $event->getTags()->map(function ($item) {
            return $item->getId();
        })->toArray() : null;
        $data['description'] = $event->getDescription();
        $data['image'] = $event->getImage();
        $data['name'] = $event->getName();
        $data['url'] = $event->getUrl();
        $data['videoUrl'] = $event->getVideoUrl();
        $data['langcode'] = $event->getLangcode();

        return $data;
    }

    private function normalizeOccurrence(Occurrence $occurrence)
    {
        $data = [];

        $data['id'] = $occurrence->getId();
        $data['event_id'] = $occurrence->getEvent() ? $occurrence->getEvent()->getId() : null;
        $data['place_id'] = $occurrence->getPlace() ? $occurrence->getPlace()->getId() : null;
        $data['startDate'] = $occurrence->getStartDate() ? $occurrence->getStartDate()->format('c') : null;
        $data['endDate'] = $occurrence->getEndDate() ? $occurrence->getEndDate()->format('c') : null;
        $data['ticketPriceRange'] = $occurrence->getTicketPriceRange();
        $data['eventStatusText'] = $occurrence->getEventStatusText();

        return $data;
    }

    private function normalizePlace(Place $place)
    {
        $data = [];

        $data['id'] = $place->getId();
        $data['logo'] = $place->getLogo();
        $data['addressLocality'] = $place->getAddressLocality();
        $data['addressRegion'] = $place->getAddressRegion();
        $data['postalCode'] = $place->getPostalCode();
        $data['streetAddress'] = $place->getStreetAddress();
        $data['occurrences'] = $place->getOccurrences();
        $data['description'] = $place->getDescription();
        $data['image'] = $place->getImage();
        $data['name'] = $place->getName();
        $data['url'] = $place->getUrl();
        $data['videoUrl'] = $place->getVideoUrl();
        $data['langcode'] = $place->getLangcode();

        return $data;
    }

    private function normalizeTag(Tag $tag)
    {
        $data = [];

        $data['id'] = $tag->getId();
        $data['name'] = $tag->getName();

        return $data;
    }
}
