<?php

namespace AdminBundle\Service\FeedReader;

use JsonPath\JsonObject;

class JsonPath extends Json {
  // http://goessner.net/articles/JsonPath/
  protected function jsonPath($data, $path, $failOnError = false) {
    $json = new JsonObject($data, true);
    return $json->get('$.' . $path);
  }

}
