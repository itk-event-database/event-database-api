<?php

namespace AppBundle\Service;

use AppBundle\Entity\Occurrence;
use PHPUnit\Framework\TestCase;

class OccurrenceSplitterServiceTest extends TestCase
{
    /**
     * @dataProvider occurrencesProvider
     *
     * @param Occurrence $occurrence
     * @param string $dateSeparatorTime
     * @param string $dateSeparatorTimezone
     * @param int $expected
     *
     * @throws \Exception
     */
    public function testGetDailyOccurrences(Occurrence $occurrence, string $dateSeparatorTime, string $dateSeparatorTimezone, int $expected): void
    {
        $splitter = new OccurrenceSplitterService($dateSeparatorTime, $dateSeparatorTimezone);

        $dailyOccurrences = $splitter->getDailyOccurrences($occurrence)->toArray();

        $this->assertCount($expected, $dailyOccurrences);

        $first = array_shift($dailyOccurrences);
        $this->assertEquals($occurrence->getStartDate(), $first->getStartDate(), 'The start dates of the occurrence and the first daily occurrence should be equal.');

        if ($expected > 1) {
            $end = array_pop($dailyOccurrences);
            $this->assertEquals($occurrence->getEndDate(), $end->getEndDate(), 'The end dates of the occurrence and the last daily occurrence should be equal.');
        }

        if ($expected === 2) {
            $this->assertEquals($first->getEndDate(), $end->getStartDate(), 'The end date and start date of two adjacent daily occurrences should be equal.');
        }

        if ($expected > 2) {
            $prev = $first;
            foreach ($dailyOccurrences as $dailyOccurrence) {
                $this->assertEquals($prev->getEndDate(), $dailyOccurrence->getStartDate(), 'The end date and start date of two adjacent daily occurrences should be equal.');
                $prev = $dailyOccurrence;
            }

            $this->assertEquals($prev->getEndDate(), $end->getStartDate(), 'The end date and start date of the last two adjacent daily occurrences should be equal.');
        }
    }

    public function occurrencesProvider(): array
    {
        $testData = [];
        $timezone = new \DateTimeZone('Europe/Copenhagen');

        // One day Occurrence
        $occurrence1 = new Occurrence();

        $startDate1 = new \DateTime();
        $startDate1->setTimezone($timezone);
        $startDate1->setDate(2010, 12, 12);
        $startDate1->setTime(8, 12, 00);

        $endDate1 = new \DateTime();
        $endDate1->setTimezone($timezone);
        $endDate1->setDate(2010, 12, 12);
        $endDate1->setTime(12, 12, 00);

        $occurrence1->setStartDate($startDate1);
        $occurrence1->setEndDate($endDate1);

        $testData[] = [$occurrence1, '03:00:00', 'Europe/Copenhagen', 1];

        // One day Occurrence -> 2 Daily Occurrences
        $occurrence1 = new Occurrence();

        $startDate1 = new \DateTime();
        $startDate1->setTimezone($timezone);
        $startDate1->setDate(2010, 12, 12);
        $startDate1->setTime(1, 12, 00);

        $endDate1 = new \DateTime();
        $endDate1->setTimezone($timezone);
        $endDate1->setDate(2010, 12, 12);
        $endDate1->setTime(12, 12, 00);

        $occurrence1->setStartDate($startDate1);
        $occurrence1->setEndDate($endDate1);

        $testData[] = [$occurrence1, '03:00:00', 'Europe/Copenhagen', 2];

        // 2 day Occurrence
        $occurrence2 = new Occurrence();

        $startDate2 = new \DateTime();
        $startDate2->setTimezone($timezone);
        $startDate2->setDate(2011, 12, 12);
        $startDate2->setTime(3, 12, 00);

        $endDate2 = new \DateTime();
        $endDate2->setTimezone($timezone);
        $endDate2->setDate(2011, 12, 13);
        $endDate2->setTime(12, 12, 00);

        $occurrence2->setStartDate($startDate2);
        $occurrence2->setEndDate($endDate2);

        $testData[] = [$occurrence2, '03:00:00', 'Europe/Copenhagen', 2];

        // 3 day Occurrence
        $occurrence3 = new Occurrence();

        $startDate3 = new \DateTime();
        $startDate3->setTimezone($timezone);
        $startDate3->setDate(2010, 12, 12);
        $startDate3->setTime(3, 12, 00);

        $endDate3 = new \DateTime();
        $endDate3->setTimezone($timezone);
        $endDate3->setDate(2010, 12, 14);
        $endDate3->setTime(12, 12, 00);

        $occurrence3->setStartDate($startDate3);
        $occurrence3->setEndDate($endDate3);

        $testData[] = [$occurrence3, '03:00:00', 'Europe/Copenhagen', 3];

        // 30 day Occurrence
        $occurrence30 = new Occurrence();

        $startDate30 = new \DateTime();
        $startDate30->setTimezone($timezone);
        $startDate30->setDate(2019, 01, 12);
        $startDate30->setTime(3, 12, 00);

        $endDate30 = new \DateTime();
        $endDate30->setTimezone($timezone);
        $endDate30->setDate(2019, 02, 10);
        $endDate30->setTime(12, 12, 00);

        $occurrence30->setStartDate($startDate30);
        $occurrence30->setEndDate($endDate30);

        $testData[] = [$occurrence30, '03:00:00', 'Europe/Copenhagen', 30];

        return $testData;
    }
}
