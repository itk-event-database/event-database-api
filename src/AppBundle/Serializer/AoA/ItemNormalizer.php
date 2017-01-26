<?php

namespace AppBundle\Serializer\AoA;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;
use AppBundle\Entity\Place;
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
  const FORMAT = 'jsonaoa';

  /**
   * @var TagManager
   */
  private $tagManager;

  public function __construct(PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory, PropertyMetadataFactoryInterface $propertyMetadataFactory, IriConverterInterface $iriConverter, ResourceClassResolverInterface $resourceClassResolver, PropertyAccessorInterface $propertyAccessor = null, NameConverterInterface $nameConverter = null, TagManager $tagManager) {
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
    $data = parent::normalize($object, $format, $context);
    if (!is_array($data)) {
      return $data;
    }

    if ($object instanceof Event) {
      $data = $this->normalizeEvent($object, $data);
    }

    return $data;
  }

  private function normalizeEvent(Event $event, array $normalized) {
    $data = [];
    $data['event_id'] = $event->getId();

    $data['category'] = null;
    $data['category_id'] = null;
    if ($event->getTags()->count() > 0) {
      $tag = $event->getTags()->first();
      $data['category'] = $tag->getName();
      $data['category_id'] = $tag->getId();
    }

    $startTime = null;
    $endTime = null;
    $location = null;
    foreach ($event->getOccurrences() as $index => $occurrence) {
      if ($startTime === null || ($occurrence->getStartDate() < $startTime)) {
        $startTime = $occurrence->getStartDate();
      }
      if ($endTime === null || ($occurrence->getEndDate() > $endTime)) {
        $endTime = $occurrence->getEndDate();
      }

      if ($index === 0) {
        /** @var Place $place */
        $place = $occurrence->getPlace();
        if ($place) {
          $location = [
            'id' => $place->getId(),
            'name' => $place->getName(),
            'street' => $place->getStreetAddress(),
            'postal_code' => $place->getPostalCode(),
            'city' => $place->getAddressLocality(),
            'phone' => $place->getTelephone(),
            'web_address' => $place->getUrl(),
            'lat' => $place->getLatitude(),
            'lng' => $place->getLongitude(),
            'details' => [],
          ];
        }
      }

      $date = null;
      if ($occurrence->getStartDate()) {
        $dayName = ['Søndag', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag'][$occurrence->getStartDate()->format('w')];
        $date = $dayName . ', ' . $occurrence->getStartDate()->format('Y-m-d');
      }
      $location['details'][$occurrence->getId()] = [
        'date' => $date,
				'time_start' => $occurrence->getStartDate() ? $occurrence->getStartDate()->format('H:i') : null,
				'time_end' => $occurrence->getEndDate() ? $occurrence->getEndDate()->format('H:i') : null,
      ];
    }
    $data['start_time'] = $startTime ? $startTime->format(\DateTime::RFC2822) : null;
    $data['end_time'] = $endTime ? $endTime->format(\DateTime::RFC2822) : null;

    $data['title'] = $normalized['name'];
    $data['supertitle'] = null;
    $data['summary'] = $normalized['excerpt'];
    $data['body_text'] = $normalized['description'];

    $data['images'] = [
      'image' => $normalized['image'],
      'image_full' => $normalized['image'],
      'caption' => null,
    ];

    $data['location'] = $location;

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDenormalization($data, $type, $format = NULL) {
    return false;
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
