<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Controller;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\RequestStack;

class OccurrencesCalendar
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Split occurrences into daily occurrences.
     *
     * @param $data
     *
     * @return array|Paginator
     */
    public function __invoke($data)
    {
        $timeZone = new \DateTimeZone('UTC');

        try {
            $timeZone = new \DateTimeZone($this->requestStack->getCurrentRequest()->get('timezone', 'UTC'));
        } catch (\Exception $e) {
        }

        $occurrences = [];
        foreach ($data->getIterator() as $occurrence) {
            /** @var $occurrence Occurrence */
            $startTime = clone $occurrence->getStartDate();
            $startTime->setTimezone($timeZone);
            $endTime = clone $occurrence->getEndDate();
            $endTime->setTimezone($timeZone);
            $nextDay = new \DateTime($startTime->format(\DateTime::ATOM).' tomorrow');
            if ($nextDay > $endTime) {
                $occurrences[] = $occurrence;
            } else {
                $endDay = new \DateTime($endTime->format(\DateTime::ATOM).' today');
                while ($nextDay <= $endDay) {
                    $dayOccurrence = clone $occurrence;
                    $dayOccurrence->setStartDate($startTime);
                    $dayOccurrence->setEndDate($nextDay);
                    $occurrences[] = $dayOccurrence;

                    $startTime = $nextDay;
                    $nextDay = new \DateTime($nextDay->format(\DateTime::ATOM).' tomorrow');
                }
                if ($endDay < $endTime) {
                    $dayOccurrence = clone $occurrence;
                    $dayOccurrence->setStartDate($endDay);
                    $dayOccurrence->setEndDate($endTime);
                    $occurrences[] = $dayOccurrence;
                }
            }
        }

        if ($data instanceof Paginator) {
            // Inject occurrences into paginator.
            $class = new \ReflectionClass($data);
            $iterator = $class->getProperty('iterator');
            $iterator->setAccessible(true);
            $iterator->setValue($data, new ArrayCollection($occurrences));

            return $data;
        }

        return $occurrences;
    }
}
