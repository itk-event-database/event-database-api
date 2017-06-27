<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends Controller {
  /**
   * @Route("/api/events/deleted.{_format}",
   *   name="api_events_deleted",
   *   defaults={"_format"="json"},
   *   requirements={
   *     "_format": "json"
   *   }
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

    return $this->createResponse($data);
  }

  /**
   * @Route(
   *   "/api/places/deleted.{_format}",
   *   name="api_places_deleted",
   *   defaults={"_format"="json"},
   *   requirements={
   *     "_format": "json|xml"
   *   }
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

    return $this->createResponse($data);
  }

  /**
   * @Route(
   *   "/api/organizers/deleted.{_format}",
   *   name="api_organizers_deleted",
   *   defaults={"_format"="json"},
   *   requirements={
   *     "_format": "json|xml"
   *   }
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

    return $this->createResponse($data);
  }

  private function createResponse($data, $format = 'json') {
    return new JsonResponse($data);
  }

  private function formatDateTime(string $time = NULL) {
    return $time ? \DateTime::createFromFormat('Y-m-d H:i:s', $time, new \DateTimeZone('UTC'))->format(\DateTime::W3C) : NULL;
  }
}
