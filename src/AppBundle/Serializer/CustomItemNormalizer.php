<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Serializer;

use AdminBundle\Factory\OrganizerFactory;
use AdminBundle\Factory\PlaceFactory;
use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use ApiPlatform\Core\JsonLd\ContextBuilderInterface;
use ApiPlatform\Core\JsonLd\Serializer\JsonLdContextTrait;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Serializer\ContextTrait;
use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;
use DoctrineExtensions\Taggable\Taggable;
use FPN\TagBundle\Entity\TagManager;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Class CustomItemNormalizer.
 *
 * This is an almost verbatim copy of.
 *
 * final class ApiPlatform\Core\JsonLd\Serializer\ItemNormalizer
 *
 * with handling of tags and places added.
 */
class CustomItemNormalizer extends AbstractItemNormalizer
{
    use ContextTrait;
    use JsonLdContextTrait;

    const FORMATS = ['json', 'jsonld', 'xml'];

    private $resourceMetadataFactory;
    private $contextBuilder;

    /**
     * @var TagManager
     */
    private $tagManager;

    /**
     * @var OrganizerFactory
     */
    private $organizerFactory;

    /**
     * @var PlaceFactory
     */
    private $placeFactory;

    public function __construct(ResourceMetadataFactoryInterface $resourceMetadataFactory, PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory, PropertyMetadataFactoryInterface $propertyMetadataFactory, IriConverterInterface $iriConverter, ResourceClassResolverInterface $resourceClassResolver, ContextBuilderInterface $contextBuilder, PropertyAccessorInterface $propertyAccessor = null, NameConverterInterface $nameConverter = null, ClassMetadataFactoryInterface $classMetadataFactory = null, TagManager $tagManager, OrganizerFactory $organizerFactory, PlaceFactory $placeFactory)
    {
        parent::__construct($propertyNameCollectionFactory, $propertyMetadataFactory, $iriConverter, $resourceClassResolver, $propertyAccessor, $nameConverter, $classMetadataFactory);

        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->contextBuilder = $contextBuilder;
        $this->tagManager = $tagManager;
        $this->organizerFactory = $organizerFactory;
        $this->placeFactory = $placeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return in_array($format, self::FORMATS, true) && parent::supportsNormalization($data, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $resourceClass = $this->resourceClassResolver->getResourceClass($object, $context['resource_class'] ?? null, true);
        $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
        $data = $this->addJsonLdContext($this->contextBuilder, $resourceClass, $context);

        // Use resolved resource class instead of given resource class to support multiple inheritance child types
        $context['resource_class'] = $resourceClass;
        $context['iri'] = $this->iriConverter->getIriFromItem($object);

        $rawData = parent::normalize($object, $format, $context);
        if (!\is_array($rawData)) {
            return $rawData;
        }

        $data['@id'] = $context['iri'];
        $data['@type'] = $resourceMetadata->getIri() ?: $resourceMetadata->getShortName();

        return $data + $rawData;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($format, self::FORMATS, true) && parent::supportsDenormalization($data, $type, $format);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        // Avoid issues with proxies if we populated the object
        if (isset($data['@id']) && !isset($context[self::OBJECT_TO_POPULATE])) {
            if (isset($context['api_allow_update']) && true !== $context['api_allow_update']) {
                throw new InvalidArgumentException('Update is not allowed for this operation.');
            }

            $context[self::OBJECT_TO_POPULATE] = $this->iriConverter->getItemFromIri($data['@id'], $context + ['fetch_data' => true]);
        }

        return parent::denormalize($data, $class, $format, $context);
    }

    protected function setAttributeValue($object, $attribute, $value, $format = null, array $context = [])
    {
        // @TODO: We should delegate this to our factories or a service.
        if ($object instanceof Taggable && 'tags' === $attribute) {
            $this->tagManager->setTags($value, $object);

            return;
        }
        if ($object instanceof Occurrence && 'place' === $attribute) {
            if (is_array($value) && empty($value['@id'])) {
                // Get unidentified place (with no specified id) from factory.
                $place = $this->placeFactory->get($value);
                if ($place) {
                    $object->setPlace($place);

                    return;
                }
            }
        }
        if ($object instanceof Event && 'organizer' === $attribute) {
            if (is_array($value) && empty($value['@id'])) {
                // Get unidentified organizer (with no specified id) from factory.
                $organizer = $this->organizerFactory->get($value);
                if ($organizer) {
                    $object->setOrganizer($organizer);

                    return;
                }
            }
        }
        if ($object instanceof Event && 'partnerOrganizers' === $attribute) {
            if (is_array($value)) {
                // Try to map all items without an `@id` key to an organizer
                // provided by the organizer factory.
                $value = array_map(
                    function ($data) {
                        if (is_array($data) && empty($data['@id'])) {
                            // Get unidentified organizer (with no specified id) from factory.
                            $organizer = $this->organizerFactory->get($data);
                            if ($organizer) {
                                return sprintf('/api/organizers/%s', $organizer->getId());
                            }
                        }

                        // We have an `@id` or the factory cannot provide an organizer.
                        return $data;
                    },
                    $value
                );
            }
        }

        parent::setAttributeValue($object, $attribute, $value, $format, $context);
    }

    protected function getAttributeValue($object, $attribute, $format = null, array $context = [])
    {
        if ($object instanceof Taggable && 'tags' === $attribute) {
            return $object->getTags()->map(function ($tag) {
                return $tag->getName();
            });
        }

        return parent::getAttributeValue($object, $attribute, $format, $context);
    }
}
