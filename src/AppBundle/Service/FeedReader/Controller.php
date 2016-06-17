<?php

namespace AppBundle\Service\FeedReader;

interface Controller {
  public function createEvent(array $data);
}