<?php

namespace AppBundle\Helper;

use AppBundle\Entity\Occurrence;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class EventHelper
{
    public static function getUpdateOccurrences($exitingOccurrences, $newOccurrences)
    {
        if (empty($exitingOccurrences)) {
            return $newOccurrences;
        }

        if (is_array($exitingOccurrences)) {
            $exitingOccurrences = new ArrayCollection($exitingOccurrences);
        } elseif ($exitingOccurrences instanceof Collection) {
            $exitingOccurrences = new ArrayCollection($exitingOccurrences->toArray());
        }
        if (!$exitingOccurrences instanceof ArrayCollection) {
            throw new \RuntimeException('exitingOccurrences must be an ArrayCollection');
        }

        if (is_array($newOccurrences)) {
            $newOccurrences = new ArrayCollection($newOccurrences);
        }
        if (!$newOccurrences instanceof ArrayCollection) {
            throw new \RuntimeException('newOccurrences must be an ArrayCollection');
        }

        if ($exitingOccurrences == $newOccurrences) {
            return $exitingOccurrences;
        }

        $exitingOccurrences = self::sortOccurrences($exitingOccurrences);
        $newOccurrences = self::sortOccurrences($newOccurrences);

        $updatedOccurrences = new ArrayCollection();
        $newNewOccurrences = new ArrayCollection();

        // Find index in exiting occurrences for each new occurrence.
        foreach ($newOccurrences as $newOccurrenceIndex => $newOccurrence) {
            $matchingIndex = null;
            for ($level = 4; $level > 0; $level--) {
                foreach ($exitingOccurrences as $index => $exitingOccurrence) {
                    if (!$updatedOccurrences->containsKey($index)
                        && self::areSameOccurrence($exitingOccurrence, $newOccurrence, $level)) {
                        if ($exitingOccurrence->getId() !== null) {
                            $exitingOccurrence
                                ->setStartDate($newOccurrence->getStartDate())
                                ->setEndDate($newOccurrence->getEndDate())
                                ->setPlace($newOccurrence->getPlace())
                                ->setRoom($newOccurrence->getRoom());
                            $updatedOccurrences->set($index, $exitingOccurrence);
                        } else {
                            $updatedOccurrences->set($index, $newOccurrence);
                        }
                        $matchingIndex = $index;

                        break 2;
                    }
                }
            }

            if ($matchingIndex === null) {
                $newNewOccurrences->add($newOccurrence);
            } else {
                $exitingOccurrences->remove($matchingIndex);
            }
        }

        // Add new occurrences not already added to updated occurrences.
        foreach ($newNewOccurrences as $occurrence) {
            $updatedOccurrences->add($occurrence);
        }

        return $updatedOccurrences;
    }


    // Radix like sort of occurrences by (in order): startDate, endEnd, place, room
    // @FIXME: Is this really needed, i.e. do we need to sort occurrences
    // before updating/merging?
    private static function sortOccurrences(ArrayCollection $occurrences)
    {
        return $occurrences;
    }

    private static function areSameOccurrence(Occurrence $a, Occurrence $b, $level)
    {
        return ($level < 1 || $a->getStartDate() == $b->getStartDate())
            && ($level < 2 || $a->getEndDate() == $b->getEndDate())
            && ($level < 3 || $a->getPlace() == $b->getPlace())
            && ($level < 4 || $a->getRoom() == $b->getRoom());
    }

    private static function renderOccurrences(ArrayCollection $occurrences)
    {
        foreach ($occurrences as $key => $occurrence) {
            $result[$key] = $occurrence->__toString();
        }

        return isset($result) ? $result : null;
    }
}
