<?php

namespace AdminBundle\Service\FeedReader;

/**
 *
 */
interface Controller {

  /**
   * @param array $data
   * @return
   */
  public function createEvent(array $data);

  /**
   * @param $value
   * @param $name
   * @return
   */
  public function convertValue($value, $name);

}
