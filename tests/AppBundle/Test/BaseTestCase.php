<?php

namespace Tests\AppBundle\Test;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BaseTestCase extends KernelTestCase {
  protected function assertSimilar(array $expected, array $actual, string $message = '') {
    $this->assertEquals($expected, $actual, $message, 0.0, 10, true);
  }
}
