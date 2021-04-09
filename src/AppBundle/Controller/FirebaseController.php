<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class FirebaseController extends Controller
{
    /**
     * @Route("/api/firebase/events")
     */
    public function eventsAction()
    {
        $sql = 'select id, event_id from occurrence where end_date >= :now or end_date is null';
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
        $stmt->execute(['now' => $this->getNow()]);
        $eventOccurrences = [];
        while ($row = $stmt->fetch()) {
            $eventId = (int) $row['event_id'];
            if (!isset($eventOccurrences[$eventId])) {
                $eventOccurrences[$eventId] = [];
            }
            $eventOccurrences[$eventId][] = (int) $row['id'];
        }

        $sql = 'select tag_id id, resource_id event_id from tagging where resource_type = \'event\'';
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
        $stmt->execute();
        $eventTags = [];
        while ($row = $stmt->fetch()) {
            $eventId = (int) $row['event_id'];
            if (!isset($eventTags[$eventId])) {
                $eventTags[$eventId] = [];
            }
            $eventTags[$eventId][] = (int) $row['id'];
        }

        $sql = 'select * from event where id in (select event_id from occurrence where end_date >= :now or end_date is null)';
        $sql .= ' and deleted_at is null and is_published = 1 and and has_full_access = 1';
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
        $stmt->execute(['now' => $this->getNow()]);
        $data = [];
        while ($row = $stmt->fetch()) {
            $data[] = [
            'id' => (int) $row['id'],
            'organizer_id' => (int) $row['organizer_id'],
            'occurrences' => $eventOccurrences[$row['id']],
            'ticketPurchaseUrl' => $row['ticket_purchase_url'],
            'excerpt' => $row['excerpt'],
            'tags' => isset($eventTags[$row['id']]) ? $eventTags[$row['id']] : null,
            'description' => $row['description'],
            'image' => $row['image'],
            'name' => $row['name'],
            'url' => $row['url'],
            'videoUrl' => $row['video_url'],
            'langcode' => $row['langcode'],
            ];
        }

        return $this->createResponse($data);
    }

    /**
     * @Route("/api/firebase/events/deleted")
     */
    public function eventsDeletedAction()
    {
        $sql = 'select * from event where deleted_at is not null and and has_full_access = 1';
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
     * @Route("/api/firebase/organizers")
     */
    public function organizersAction()
    {
        $sql = 'select * from organizer where deleted_at is null';
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
        $stmt->execute();
        $data = [];
        while ($row = $stmt->fetch()) {
            $data[] = [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'url' => $row['url'],
            ];
        }

        return $this->createResponse($data);
    }

    /**
     * @Route("/api/firebase/organizers/deleted")
     */
    public function organizersDeletedAction()
    {
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

    /**
     * @Route("/api/firebase/occurrences")
     */
    public function occurrencesAction()
    {
        $sql = 'select * from occurrence where (end_date >= :now or end_date is null)';
        $sql .= ' and event_id in (select id from event where deleted_at is null and is_published = 1 and has_full_access = 1)';
        $sql .= ' order by start_date';
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
        $stmt->execute(['now' => $this->getNow()]);
        $data = [];
        while ($row = $stmt->fetch()) {
            $data[] = [
            'id' => (int) $row['id'],
            'event_id' => (int) $row['event_id'],
            'place_id' => (int) $row['place_id'],
            'startDate' => $this->formatDateTime($row['start_date']),
            'endDate' => $this->formatDateTime($row['end_date']),
            'ticketPriceRange' => $row['ticket_price_range'],
            'eventStatusText' => $row['event_status_text'],
            ];
        }

        return $this->createResponse($data);
    }

    /**
     * @Route("/api/firebase/places")
     */
    public function placesAction()
    {
        $sql = 'select * from place where deleted_at is null';
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
        $stmt->execute();
        $data = [];
        while ($row = $stmt->fetch()) {
            $data[] = [
            'id' => (int) $row['id'],
            'logo' => $row['logo'],
            'addressLocality' => $row['address_locality'],
            'addressRegion' => $row['address_region'],
            'postalCode' => $row['postal_code'],
            'streetAddress' => $row['street_address'],
            'occurrences' => (object) [],
            'description' => $row['description'],
            'image' => $row['image'],
            'name' => $row['name'],
            'url' => $row['url'],
            'videoUrl' => $row['video_url'],
            'langcode' => $row['langcode'],
            ];
        }

        return $this->createResponse($data);
    }

    /**
     * @Route("/api/firebase/places/deleted")
     */
    public function placesDeletedAction()
    {
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
     * @Route("/api/firebase/tags")
     */
    public function tagsAction()
    {
        $sql = 'select * from tag';
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);
        $stmt->execute(['now' => $this->getNow()]);
        $data = [];
        while ($row = $stmt->fetch()) {
            $data[] = [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            ];
        }

        return $this->createResponse($data);
    }

    private function createResponse($data)
    {
        return new JsonResponse($data, 200, [
        'Content-type' => 'application/firebase+json; charset=utf-8',
        ]);
    }

    private function formatDateTime(string $time = null)
    {
        return $time ? \DateTime::createFromFormat('Y-m-d H:i:s', $time, new \DateTimeZone('UTC'))->format(\DateTime::W3C) : null;
    }

    private function getNow()
    {
        return (new \DateTime(null, new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    }
}
