<?php

namespace AdminBundle\Service\FeedReader;

interface Controller {
  public function createEvent(array $data);

  public function convertValue($value, $name);
}