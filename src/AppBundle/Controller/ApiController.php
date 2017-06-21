<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * @Rest\View(serializerGroups={"api"})
 */
class ApiController extends FOSRestController {
  /**
   * @Rest\Get(
   *     "/api/events/deleted.{_format}",
   *     name="api_events_deleted",
   *     defaults={"_format"="json"},
   *     requirements={
   *         "_format": "json|xml"
   *     }
   * )
   */
  public function eventsDeletedAction() {
    $sql = 'select * from event where deleted_at is not null';
    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
    $stmt->execute();
    $data = [];
    while ($row = $stmt->fetch()) {
      $data[] = [
        'id' => (int) $row['id'],
        'deletedAt' => $this->formatDateTime($row['deleted_at']),
      ];
    }

    return $data;
  }

  /**
   * @Rest\Get(
   *     "/api/places/deleted.{_format}",
   *     name="api_places_deleted",
   *     defaults={"_format"="json"},
   *     requirements={
   *         "_format": "json|xml"
   *     }
   * )
   */
  public function placesDeletedAction() {
    $sql = 'select * from place where deleted_at is not null';
    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
    $stmt->execute();
    $data = [];
    while ($row = $stmt->fetch()) {
      $data[] = [
        'id' => (int) $row['id'],
        'deletedAt' => $this->formatDateTime($row['deleted_at']),
      ];
    }

    return $data;
  }

  /**
   * @Rest\Get(
   *     "/api/organizers/deleted.{_format}",
   *     name="api_organizers_deleted",
   *     defaults={"_format"="json"},
   *     requirements={
   *         "_format": "json|xml"
   *     }
   * )
   */
  public function organizersDeletedAction() {
    $sql = 'select * from organizer where deleted_at is not null';
    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
    $stmt->execute();
    $data = [];
    while ($row = $stmt->fetch()) {
      $data[] = [
        'id' => (int) $row['id'],
        'deletedAt' => $this->formatDateTime($row['deleted_at']),
      ];
    }

    return $data;
  }

  private function formatDateTime(string $time = NULL) {
    return $time ? \DateTime::createFromFormat('Y-m-d H:i:s', $time, new \DateTimeZone('UTC'))->format(\DateTime::W3C) : NULL;
  }
}
