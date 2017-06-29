<?php

namespace AdminBundle\Service;

use AdminBundle\Entity\Feed;
use AppBundle\Entity\User;

/**
 *
 */
class FeedValidator extends FeedPreviewer
{

  /**
   * @param \AdminBundle\Entity\Feed $feed
   * @return array
   */
    public function validate(Feed $feed, User $user = null)
    {
        parent::read($feed, $user);
        return $this->validateEvents($this->events);
    }

    protected function validateEvents(array &$events)
    {
        $errors = [];

        if ($events) {
            foreach ($events as $index => &$event) {
                $eventErrors = $this->validateEvent($event);
                if ($eventErrors) {
                    $errors[$index] = $eventErrors;
                }
            }
        }

        return $errors;
    }

    protected function validateEvent(array &$event)
    {
        $errors = [];

        $this->requireValues($event, ['id', 'langcode', 'organizer', 'occurrences'], $errors);

        if (!empty($event['organizer'])) {
            $organizerErrors = $this->validateOrganizer($event['organizer']);
            if ($organizerErrors) {
                $errors['organizer'] = $organizerErrors;
            }
        }

        if (!empty($event['occurrences'])) {
            $occurrenceErrors = $this->validateOccurrences($event['occurrences']);
            if ($occurrenceErrors) {
                $errors['occurrences'] = $occurrenceErrors;
            }
        }

        if ($errors) {
            $event['__validation_errors__'] = $errors;
        }

        return $errors;
    }

    protected function validateOrganizer(array &$organizer)
    {
        $errors = [];

        $this->requireValues($organizer, ['name', 'url', 'email'], $errors);

        if ($errors) {
            $organizer['__validation_errors__'] = $errors;
        }

        return $errors;
    }

    protected function validateOccurrences(array &$occurrences)
    {
        $errors = [];

        if ($occurrences) {
            foreach ($occurrences as $index => &$occurrence) {
                $occurrenceErrors = $this->validateOccurrence($occurrence, $errors, $index);
                if ($occurrenceErrors) {
                    $errors[$index] = $occurrenceErrors;
                }
            }
        }

        return $errors;
    }

    protected function validateOccurrence(array &$occurrence)
    {
        $errors = [];

        $this->requireValues($occurrence, ['startDate', 'endDate'], $errors);
        if (!empty($occurrence['startDate']) && !empty($occurrence['endDate']) && $occurrence['startDate'] >= $occurrence['endDate']) {
            $errors[] = sprintf('End date (%s) must be after start date (%s)', $occurrence['startDate']->format('c'), $occurrence['endDate']->format('c'));
        }

        $this->requireValues($occurrence, ['place'], $errors);
        if (!empty($occurrence['place'])) {
            $placeErrors = $this->validatePlace($occurrence['place']);
            if ($placeErrors) {
                $errors['place'] = $placeErrors;
            }
        }

        if ($errors) {
            $occurrence['__validation_errors__'] = $errors;
        }

        return $errors;
    }

    protected function validatePlace(array &$place)
    {
        $errors = [];

        $this->requireValues($place, ['name', 'street_address'], $errors);

        if ($errors) {
            $place['__validation_errors__'] = $errors;
        }

        return $errors;
    }

    protected function requireValues(array $data, array $keys, array &$errors)
    {
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                $errors[] = sprintf('Missing data: %s', $key);
            } elseif (empty($data[$key])) {
                $errors[] = sprintf('Invalid data: %s: %s', $key, json_encode($data[$key]));
            }
        }
    }
}
