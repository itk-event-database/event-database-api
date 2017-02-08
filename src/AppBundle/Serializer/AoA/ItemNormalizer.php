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

    $data['category'] = 'Byliv';
    $data['category_id'] = 64237;

    $eventStartTime = null;
    $eventEndTime = null;
    $location = null;
    foreach ($event->getOccurrences() as $index => $occurrence) {
      $startTime = $occurrence->getStartDate();
      $endTime = $occurrence->getEndDate();
      if (!$endTime && $startTime) {
        $endTime = clone $startTime;
        $endTime->add(new \DateInterval('PT1H'));
      }

      if ($eventStartTime === null || $startTime < $eventStartTime) {
        $eventStartTime = $startTime;
      }
      if ($eventEndTime === null || $endTime > $eventEndTime) {
        $eventEndTime = $endTime;
      }

      if ($index === 0) {
        /** @var Place $place */
        $place = $occurrence->getPlace();
        if ($place) {
          $location = [
            'id' => $place->getId(),
            'name' => $place->getName() ?: '',
            'street' => $place->getStreetAddress() ?: '',
            'postal_code' => $place->getPostalCode() ?: '',
            'city' => $place->getAddressLocality() ?: '',
            'phone' => $place->getTelephone() ?: '',
            'web_address' => $place->getUrl() ?: '',
            'lat' => $place->getLatitude() ?: '',
            'lng' => $place->getLongitude() ?: '',
            'details' => [],
          ];
        }
      }

      $date = '';
      if ($occurrence->getStartDate()) {
        $dayName = ['Søndag', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag'][$occurrence->getStartDate()->format('w')];
        $date = $dayName . ', ' . $occurrence->getStartDate()->format('Y-m-d');
      }
      $location['details'][$occurrence->getId()] = [
        'date' => $date,
				'time_start' => $startTime ? $startTime->format('H:i') : '',
				'time_end' => $endTime ? $endTime->format('H:i') : '',
      ];
    }
    $data['start_time'] = $eventStartTime ? $eventStartTime->format(\DateTime::RFC2822) : '';
    $data['end_time'] = $eventEndTime ? $eventEndTime->format(\DateTime::RFC2822) : '';

    $data['title'] = $normalized['name'] ?: '';
    $data['supertitle'] = '';

    $data['summary'] = $normalized['excerpt'] ?: '';
    $data['body_text'] = $normalized['description'] ?: '';

    $data['images'] = [
      'image' => $normalized['image'] ?: '',
      'image_full' => $normalized['image'] ?: '',
      'caption' => '',
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
