<?php

namespace AppBundle\Entity;

use Tests\AppBundle\Test\DatabaseTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class OccurrenceTest extends DatabaseTestCase
{

    public function testPlace()
    {
        $occurrence = new Occurrence();
        $place = new Place();
        $occurrence->setPlace($place);
        $this->em->persist($place);
        $this->em->persist($occurrence);
        $this->em->flush();

        $sql = 'select * from occurrence';
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result));

        $sql = 'select * from place';
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result));
    }
}
