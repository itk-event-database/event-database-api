<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Tests\AppBundle\Test;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversNothing
 */
class BaseTestCase extends KernelTestCase
{
    protected function assertSimilar(array $expected, array $actual, string $message = '')
    {
        $this->assertEquals($expected, $actual, $message, 0.0, 10, true);
    }
}
