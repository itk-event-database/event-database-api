<?php

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
use AppBundle\Entity\CustomTaggable;
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
 *
 * @package AppBundle\Serializer
 */
class CustomItemNormalizer extends AbstractItemNormalizer {
  use ContextTrait;
  use JsonLdContextTrait;

  const FORMAT = 'jsonld';

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

  /**
   *
   */
  public function __construct(ResourceMetadataFactoryInterface $resourceMetadataFactory, PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory, PropertyMetadataFactoryInterface $propertyMetadataFactory, IriConverterInterface $iriConverter, ResourceClassResolverInterface $resourceClassResolver, ContextBuilderInterface $contextBuilder, PropertyAccessorInterface $propertyAccessor = NULL, NameConverterInterface $nameConverter = NULL, TagManager $tagManager, OrganizerFactory $organizerFactory, PlaceFactory $placeFactory) {
    parent::__construct($propertyNameCollectionFactory, $propertyMetadataFactory, $iriConverter, $resourceClassResolver, $propertyAccessor, $nameConverter);

    $this->resourceMetadataFactory = $resourceMetadataFactory;
    $this->contextBuilder = $contextBuilder;
    $this->tagManager = $tagManager;
    $this->organizerFactory = $organizerFactory;
    $this->placeFactory = $placeFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    return self::FORMAT === $format && parent::supportsNormalization($data, $format);
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $resourceClass = $this->resourceClassResolver->getResourceClass($object, $context['resource_class'] ?? NULL, TRUE);
    $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
    $data = $this->addJsonLdContext($this->contextBuilder, $resourceClass, $context);

    $rawData = parent::normalize($object, $format, $context);
    if (!is_array($rawData)) {
      return $rawData;
    }

    $data['@id'] = $this->iriConverter->getIriFromItem($object);
    $data['@type'] = ($iri = $resourceMetadata->getIri()) ? $iri : $resourceMetadata->getShortName();

    return array_merge($data, $rawData);
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDenormalization($data, $type, $format = NULL) {
    return self::FORMAT === $format && parent::supportsDenormalization($data, $type, $format);
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    // Avoid issues with proxies if we populated the object.
    if (isset($data['@id']) && !isset($context['object_to_populate'])) {
      $context['object_to_populate'] = $this->iriConverter->getItemFromIri($data['@id'], ['fetch_data' => true]);
    }

    return parent::denormalize($data, $class, $format, $context);
  }

  /**
   *
   */
  protected function setAttributeValue($object, $attribute, $value, $format = NULL, array $context = []) {
    // @TODO: We should delegate this to our factories or a service.
    if ($object instanceof Taggable && $attribute === 'tags') {
      $tags = $this->tagManager->loadOrCreateTags($value);
      $this->tagManager->addTags($tags, $object);

      // Store all tags as custom tags on object.
      if ($object instanceof CustomTaggable) {
        $customTags = array_diff($value, array_map(function ($tag) {
          return $tag->getName();
        }, $tags));
        if ($customTags) {
          $object->setCustomTags($customTags);
        }
      }
      return;
    }
    if ($object instanceof Occurrence && $attribute === 'place') {
      if (is_array($value) && empty($value['@id'])) {
        // Get unidentified place (with no specified id) from factory.
        $place = $this->placeFactory->get($value);
        if ($place) {
          $object->setPlace($place);
          return;
        }
      }
    }
    if ($object instanceof Event && $attribute === 'organizer') {
      if (is_array($value) && empty($value['@id'])) {
        // Get unidentified organizer (with no specified id) from factory.
        $organizer = $this->organizerFactory->get($value);
        if ($organizer) {
          $object->setOrganizer($organizer);
          return;
        }
      }
    }
    parent::setAttributeValue($object, $attribute, $value, $format, $context);
  }

  /**
   *
   */
  protected function getAttributeValue($object, $attribute, $format = NULL, array $context = []) {
    if ($object instanceof Taggable && $attribute === 'tags') {
      return $object->getTags()->map(function ($tag) {
          return $tag->getName();
      });
    }
    return parent::getAttributeValue($object, $attribute, $format, $context);
  }

}
