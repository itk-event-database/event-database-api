<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service\FeedReader;

use AdminBundle\Entity\Feed;
use Tests\AppBundle\Test\BaseTestCase;

/**
 * @coversNothing
 */
class ValueConverterTest extends BaseTestCase
{
    private $converter;

    public function setUp()
    {
        $this->converter = new ValueConverter();
        $feed = new Feed();
        $feed->setConfiguration([
        'baseUrl' => 'http://example.com/',
        'timeZone' => 'UTC',
        ]);
        $this->converter->setFeed($feed);
    }

    /**
     * @dataProvider convertValueProvider
     *
     * @param mixed $key
     * @param mixed $value
     * @param mixed $expected
     */
    public function testConvertValue($key, $value, $expected)
    {
        $actual = $this->converter->convert($value, $key);
        if ($actual instanceof \DateTime && $expected instanceof \DateTime) {
            $actual = $actual->getTimestamp();
            $expected = $expected->getTimestamp();
        }
        $this->assertEquals($expected, $actual);
    }

    public function convertValueProvider()
    {
        return [
        ['stuff', '2000-01-01', '2000-01-01'],
        ['startDate', '2000-01-01', new \DateTime('2000-01-01T00:00:00+00:00')],
        ['startDate', '2000-01-01', new \DateTime('2000-01-01', new \DateTimeZone('UTC'))],
        ['endDate', '2000-01-01', new \DateTime('2000-01-01', new \DateTimeZone('UTC'))],
        ['url', 'images/horse.jpg', 'http://example.com/images/horse.jpg'],
        ['url', 'http://domain.com/images/horse.jpg', 'http://domain.com/images/horse.jpg'],
        ];
    }
}
