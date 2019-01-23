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
        $this->splitHour= (int) $exploded[0];
        $this->splitMinute= (int) $exploded[1];
        $this->splitSecond= (int) $exploded[2];
        $this->dateSeparatorTimezone = new \DateTimeZone($dateSeparatorTimezone);

        $propertyInfo = new ReflectionExtractor();
        $this->occurrenceTraitProperties = $propertyInfo->getProperties(OccurrenceTrait::class);

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Get new DailyOccurrences from an Occurrence
     *
     * @param Occurrence $occurrence
     *
     * @return Collection
     *
     * @throws \Exception
     */
    public function getDailyOccurrences(Occurrence $occurrence): Collection
    {
        $dailyOccurrences = new ArrayCollection();
        $this->createDailyOccurrenceCollection($occurrence, $dailyOccurrences);

        return $dailyOccurrences;
    }

    /**
     * Copy values of OccurrenceTrait properties from one DailyOccurrence to another
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
     * Create a collection of DailyOccurrences from an Occurrence
     *
     * @param Occurrence $occurrence
     * @param Collection $dailyOccurrences
     *
     * @return Collection
     *
     * @throws \Exception
     */
    private function createDailyOccurrenceCollection(Occurrence $occurrence, Collection $dailyOccurrences): Collection
    {
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

        $dailyOccurrence = $this->createDailyOccurrence($tempOccurrence->getStartDate(), $tempOccurrence->getEndDate(), $occurrence);
        $dailyOccurrences->add($dailyOccurrence);

        return $dailyOccurrences;
    }

    /**
     * Create a DailyOccurrence from an Occurrence
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
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
     * Get the first split DateTime for an Occurrence based on the configured split time
     *
     * @param \DateTime $dateTime
     *
     * @return \DateTime
     *
     * @throws \Exception
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
     * Compares if the time of the given datetime object is before the configured split time
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