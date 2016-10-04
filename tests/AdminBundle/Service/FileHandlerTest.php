<?php

namespace AdminBundle\Service;

use AdminBundle\Entity\Feed;
use Tests\AppBundle\Test\ContainerTestCase;

class FileHandlerTest extends ContainerTestCase {
  private $handler;

  public function setUp() {
    parent::setUp();
    $this->handler = $this->container->get('file_handler');
  }

  /**
   * @dataProvider testIsLocalUrlProvider
   */
  public function testIsLocalUrl($url, $expected) {
    $actual = $this->handler->isLocalUrl($url);
    $this->assertEquals($expected, $actual);
  }

  /**
   * @dataProvider testDownloadFileProvider
   */
  public function testDownloadFile($url, $expected) {
    $actual = $this->handler->download($url);
    if ($expected === null) {
      $this->assertNull($actual);
    } else {
      $this->assertStringEndsWith($expected, $actual);
    }
  }

  public function testIsLocalUrlProvider() {
    return [
      ['https://dummyimage.com/600x400/000/00ffd5.png', false],
      ['http://lorempixel.com/this-file-does-not-exist.jpg', false],
      ['//lorempixel.com/this-file-does-not-exist.jpg', false],
      ['/lorempixel.com/this-file-does-not-exist.jpg', true],
      ['this-file-does-not-exist.jpg', true],
    ];
  }

  public function testDownloadFileProvider() {
    return [
      ['http://event-database-api.vm/files/2b3fd2c4d0cb07be2ac6924244140d59.jpg', 'http://event-database-api.vm/files/2b3fd2c4d0cb07be2ac6924244140d59.jpg'],
      ['http://event-database-api.vm/hest/hyp.jpg', 'http://event-database-api.vm/hest/hyp.jpg'],
      ['https://dummyimage.com/600x400/000/00ffd5.png', '/ccc599deff838b96e1d5e5d6e7a70a1b.png'],
      ['http://lorempixel.com/this-file-does-not-exist.jpg', null],
    ];
  }
}
