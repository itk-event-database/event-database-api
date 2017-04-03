<?php

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
 *
 * @package AppBundle\Serializer
 */
class ItemNormalizer extends AbstractItemNormalizer {
  const FORMAT = 'firebase';

  /**
   * @var TagManager
   */
  private $tagManager;

  public function __construct(PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory, PropertyMetadataFactoryInterface $propertyMetadataFactory, IriConverterInterface $iriConverter, ResourceClassResolverInterface $resourceClassResolver, PropertyAccessorInterface $propertyAccessor = NULL, NameConverterInterface $nameConverter = NULL, TagManager $tagManager) {
    parent::__construct($propertyNameCollectionFactory, $propertyMetadataFactory, $iriConverter, $resourceClassResolver, $propertyAccessor, $nameConverter);
    $this->tagManager = $tagManager;
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
    if ($object instanceof Event) {
      return $this->normalizeEvent($object);
    }
    elseif ($object instanceof Occurrence) {
      return $this->normalizeOccurrence($object);
    }
    elseif ($object instanceof Place) {
      return $this->normalizePlace($object);
    }
    elseif ($object instanceof Tag) {
      return $this->normalizeTag($object);
    }

    return [];
  }

  private function normalizeEvent(Event $event) {
    $data = [];

    $data['id'] = $event->getId();
    $data['occurrences'] = $event->getOccurrences() ? $event->getOccurrences()->map(function ($item) {
      return $item->getId();
    })->toArray() : NULL;
    $data['ticketPurchaseUrl'] = $event->getTicketPurchaseUrl();
    $data['excerpt'] = $event->getExcerpt();
    $data['tags'] = $event->getTags() ? $event->getTags()->map(function ($item) {
      return $item->getId();
    })->toArray() : NULL;
    $data['description'] = $event->getDescription();
    $data['image'] = $event->getImage();
    $data['name'] = $event->getName();
    $data['url'] = $event->getUrl();
    $data['videoUrl'] = $event->getVideoUrl();
    $data['langcode'] = $event->getLangcode();

    return $data;
  }

  private function normalizeOccurrence(Occurrence $occurrence) {
    $data = [];

    $data['id'] = $occurrence->getId();
    $data['event_id'] = $occurrence->getEvent() ? $occurrence->getEvent()->getId() : NULL;
    $data['place_id'] = $occurrence->getPlace() ? $occurrence->getPlace()->getId() : NULL;
    $data['startDate'] = $occurrence->getStartDate() ? $occurrence->getStartDate()->format('c') : NULL;
    $data['endDate'] = $occurrence->getEndDate() ? $occurrence->getEndDate()->format('c') : NULL;
    $data['ticketPriceRange'] = $occurrence->getTicketPriceRange();
    $data['eventStatusText'] = $occurrence->getEventStatusText();

    return $data;
  }

  private function normalizePlace(Place $place) {
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

  private function normalizeTag(Tag $tag) {
    $data = [];

    $data['id'] = $tag->getId();
    $data['name'] = $tag->getName();

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDenormalization($data, $type, $format = NULL) {
    return FALSE;
  }

  /**
   *
   */
  protected function getAttributeValue($object, $attribute, $format = NULL, array $context = []) {
    if ($object instanceof Taggable && $attribute === 'tags') {
      $this->tagManager->loadTagging($object);
      return $object->getTags()->map(function ($tag) {
        return $tag->getName();
      });
    }

    return parent::getAttributeValue($object, $attribute, $format, $context);
  }

}
