<?php

namespace AppBundle\Serializer;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use ApiPlatform\Core\JsonLd\ContextBuilderInterface;
use ApiPlatform\Core\JsonLd\Serializer\JsonLdContextTrait;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Serializer\ContextTrait;
use DoctrineExtensions\Taggable\Taggable;
use FPN\TagBundle\Entity\TagManager;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Class CustomItemNormalizer
 *
 * This is an almost verbatim copy of
 * ApiPlatform\Core\JsonLd\Serializer\ItemNormalize
 * with handling of tags added.
 *
 * @package AppBundle\Serializer
 */
class CustomItemNormalizer extends AbstractItemNormalizer {
  use ContextTrait;
  use JsonLdContextTrait;

  const FORMAT = 'jsonld';

  private $resourceMetadataFactory;
  private $contextBuilder;
  private $tagManager;

  public function __construct(ResourceMetadataFactoryInterface $resourceMetadataFactory, PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory, PropertyMetadataFactoryInterface $propertyMetadataFactory, IriConverterInterface $iriConverter, ResourceClassResolverInterface $resourceClassResolver, ContextBuilderInterface $contextBuilder, PropertyAccessorInterface $propertyAccessor = null, NameConverterInterface $nameConverter = null, TagManager $tagManager)
  {
    parent::__construct($propertyNameCollectionFactory, $propertyMetadataFactory, $iriConverter, $resourceClassResolver, $propertyAccessor, $nameConverter);

    $this->resourceMetadataFactory = $resourceMetadataFactory;
    $this->contextBuilder = $contextBuilder;
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
    $resourceClass = $this->resourceClassResolver->getResourceClass($object, $context['resource_class'] ?? null, true);
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
  public function supportsDenormalization($data, $type, $format = null)
  {
    return self::FORMAT === $format && parent::supportsDenormalization($data, $type, $format);
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = null, array $context = [])
  {
    // Avoid issues with proxies if we populated the object
    if (isset($data['@id']) && !isset($context['object_to_populate'])) {
      $context['object_to_populate'] = $this->iriConverter->getItemFromIri($data['@id'], true);
    }

    return parent::denormalize($data, $class, $format, $context);
  }

  protected function setAttributeValue($object, $attribute, $value, $format = null, array $context = [])
  {
    if ($object instanceof Taggable && $attribute === 'tags') {
      $tags = $this->tagManager->loadOrCreateTags($value);
      $this->tagManager->addTags($tags, $object);
      return;
    }
    parent::setAttributeValue($object, $attribute, $value, $format, $context);
  }

  protected function getAttributeValue($object, $attribute, $format = null, array $context = [])
  {
    if ($object instanceof Taggable && $attribute === 'tags') {
      $this->tagManager->loadTagging($object);
      return $object->getTags()->map(function($tag) {
        return $tag->getName();
      });
    }
    return parent::getAttributeValue($object, $attribute, $format, $context);
  }
}