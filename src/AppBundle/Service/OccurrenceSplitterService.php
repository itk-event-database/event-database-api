<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Service;

use AppBundle\Entity\DailyOccurrence;
use AppBundle\Entity\Occurrence;
use AppBundle\Entity\OccurrenceTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

class OccurrenceSplitterService
{
    private $dateSeparatorTimezone;
    private $splitHour;
    private $splitMinute;
    private $splitSecond;

    private $occurrenceTraitProperties;
    private $propertyAccessor;

    /**
     * OccurrenceSplitterService constructor.
     *
     * @param string $dateSeparatorTime
     * @param string $dateSeparatorTimezone
     */
    public function __construct(string $dateSeparatorTime, string $dateSeparatorTimezone)
    {
        $exploded = explode(':', $dateSeparatorTime);
        $this->splitHour = (int) $exploded[0];
        $this->splitMinute = (int) $exploded[1];
        $this->splitSecond = (int) $exploded[2];
        $this->dateSeparatorTimezone = new \DateTimeZone($dateSeparatorTimezone);

        $propertyInfo = new ReflectionExtractor();
        $this->occurrenceTraitProperties = $propertyInfo->getProperties(OccurrenceTrait::class);

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Create a collection of DailyOccurrences in chronological order from an Occurrence.
     *
     * @param Occurrence $occurrence
     *
     * @throws \Exception
     *
     * @return Collection
     */
    public function createDailyOccurrenceCollection(Occurrence $occurrence): Collection
    {
        $dailyOccurrences = new ArrayCollection();
        if ($occurrence->getStartDate() && $occurrence->getEndDate() && $occurrence->getStartDate() <= $occurrence->getEndDate()) {
            $tempOccurrence = clone $occurrence;
            $splitDate = $this->getFirstSplitDateTime($tempOccurrence->getStartDate());

            if ($tempOccurrence->getStartDate() < $splitDate && $tempOccurrence->getEndDate() > $splitDate) {
                while ($tempOccurrence->getStartDate() < $splitDate && $tempOccurrence->getEndDate() > $splitDate) {
                    $dailyOccurrence = $this->createDailyOccurrence($tempOccurrence->getStartDate(), $splitDate, $occurrence);
                    $dailyOccurrences->add($dailyOccurrence);

                    $tempOccurrence->setStartDate($splitDate);
                    $splitDate = $this->getFirstSplitDateTime($tempOccurrence->getStartDate());
                }
            }

            if ($tempOccurrence->getStartDate() < $tempOccurrence->getEndDate()) {
                $dailyOccurrence = $this->createDailyOccurrence($tempOccurrence->getStartDate(), $tempOccurrence->getEndDate(), $occurrence);
                $dailyOccurrences->add($dailyOccurrence);
            }
        }

        return $dailyOccurrences;
    }

    /**
     * Copy values of OccurrenceTrait properties from one DailyOccurrence to another.
     *
     * @param DailyOccurrence $to
     * @param DailyOccurrence $from
     */
    public function copyOccurrenceTraitPropertyValues(DailyOccurrence $to, DailyOccurrence $from): void
    {
        foreach ($this->occurrenceTraitProperties as $propertyPath) {
            $value = $this->propertyAccessor->getValue($from, $propertyPath);
            $this->propertyAccessor->setValue($to, $propertyPath, $value);
        }
    }

    /**
     * Create a DailyOccurrence from an Occurrence.
     *
     * @param \DateTime  $startDate
     * @param \DateTime  $endDate
     * @param Occurrence $occurrence
     *
     * @return DailyOccurrence
     */
    private function createDailyOccurrence(\DateTime $startDate, \DateTime $endDate, Occurrence $occurrence): DailyOccurrence
    {
        $dailyOccurrence = new DailyOccurrence();
        foreach ($this->occurrenceTraitProperties as $propertyPath) {
            $value = $this->propertyAccessor->getValue($occurrence, $propertyPath);
            $this->propertyAccessor->setValue($dailyOccurrence, $propertyPath, $value);
        }

        $dailyOccurrence->setOccurrence($occurrence);
        $dailyOccurrence->setStartDate($startDate);
        $dailyOccurrence->setEndDate($endDate);

        return $dailyOccurrence;
    }

    /**
     * Get the first split DateTime for an Occurrence based on the configured split time.
     *
     * @param \DateTime $dateTime
     *
     * @throws \Exception
     *
     * @return \DateTime
     */
    private function getFirstSplitDateTime(\DateTime $dateTime): \DateTime
    {
        $split = clone $dateTime;
        $split->setTimezone($this->dateSeparatorTimezone);
        $split->setTime($this->splitHour, $this->splitMinute, $this->splitSecond);

        if ($this->isAfterSplitTime($dateTime)) {
            $oneDay = new \DateInterval('P1D');
            $split->add($oneDay);
        }

        $split->setTimezone($dateTime->getTimezone());

        return $split;
    }

    /**
     * Compares if the time of the given datetime object is before the configured split time.
     *
     * @param \DateTime $dateTime
     *
     * @return bool
     */
    private function isAfterSplitTime(\DateTime $dateTime): bool
    {
        $split = clone $dateTime;
        $split->setTimezone($this->dateSeparatorTimezone);
        $split->setTime($this->splitHour, $this->splitMinute, $this->splitSecond);

        return $dateTime >= $split;
    }
}
