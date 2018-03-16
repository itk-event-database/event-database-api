<?php

namespace tests\AppBundle\Helper;

use AppBundle\Entity\Event;
use AppBundle\Entity\Occurrence;
use AppBundle\Entity\Place;
use AppBundle\Helper\EventHelper;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tests\AppBundle\Test\ContainerTestCase;

class EventHelperTest extends ContainerTestCase
{
    public function testStuff1()
    {
        $existingOccurrences = [
            $this->createOccurrence(1, '2001-01-01', '2001-01-02', null, null),
            $this->createOccurrence(2, '2001-01-01', '2001-01-02', null, null),
        ];

        $newOccurrences = [
            $this->createOccurrence(null, '2001-01-01', '2001-01-02', null, null),
        ];

        $updatedOccurrences = EventHelper::getUpdateOccurrences($existingOccurrences, $newOccurrences);

        $this->assertEquals(1, count($updatedOccurrences));
        $this->assertEquals(count($updatedOccurrences), count($newOccurrences));
        $this->assertSame(1, $existingOccurrences[0]->getId());
    }

    public function testStuff2()
    {
        $existingOccurrences = [
            $this->createOccurrence(1, '2001-01-01', '2001-01-02', null, null),
            $this->createOccurrence(2, '2001-01-01', '2001-01-02', null, null),
        ];

        $newOccurrences = [
            $this->createOccurrence(null, '2001-01-01', '2001-01-03', null, null),
        ];

        $updatedOccurrences = EventHelper::getUpdateOccurrences($existingOccurrences, $newOccurrences);

        $this->assertEquals(1, count($updatedOccurrences));
        $this->assertEquals(count($updatedOccurrences), count($newOccurrences));
        $this->assertSame(1, $existingOccurrences[0]->getId());
    }

    public function testStuff3()
    {
        $existingOccurrences = [
            $this->createOccurrence(1, '2001-01-01', '2001-01-02', null, null),
            $this->createOccurrence(2, '2001-01-01', '2001-01-02', null, null),
        ];

        $newOccurrences = [
            $this->createOccurrence(null, '2001-01-02', '2001-01-03', null, null),
        ];

        $updatedOccurrences = EventHelper::getUpdateOccurrences($existingOccurrences, $newOccurrences);

        $this->assertEquals(1, count($updatedOccurrences));
        $this->assertEquals(count($updatedOccurrences), count($newOccurrences));
        $this->assertSame(1, $existingOccurrences[0]->getId());
    }

    private function createOccurrence($id, $startTime, $endTime, $place, $room)
    {
        $occurrence = new Occurrence();

        if ($id !== null) {
            $prop = new \ReflectionProperty(Occurrence::class, 'id');
            $prop->setAccessible(true);
            $prop->setValue($occurrence, $id);
        }

        $occurrence
            ->setStartDate(new \DateTime($startTime))
            ->setEndDate(new \DateTime($endTime))
            ->setPlace($place)
            ->setRoom($room);

        return $occurrence;
    }

    private function createPlace($id, $name)
    {
        $place = new Place();

        if ($id !== null) {
            $prop = new \ReflectionProperty(Place::class, 'id');
            $prop->setAccessible(true);
            $prop->setValue($place, $id);
        }

        $place
            ->setName($name);

        return $place;
    }
}
