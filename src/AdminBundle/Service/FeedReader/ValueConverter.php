<?php

namespace AdminBundle\Service\FeedReader;

use AdminBundle\Entity\Feed;
use League\Uri\Modifiers\Resolve;
use League\Uri\Schemes\Http as HttpUri;

class ValueConverter {
  protected $feed;
  protected $urlResolver;

  public function setFeed(Feed $feed) {
    $this->feed = $feed;
    $this->urlResolver = $this->feed->getBaseUrl() ? new Resolve(HttpUri::createFromString($this->feed->getBaseUrl())) : null;
  }

  public function convert($value, $name) {
    switch ($name) {
      case 'startDate':
      case 'endDate':
        return $this->parseDate($value);

      case 'image':
      case 'url':
        if ($this->urlResolver) {
          $relativeUrl = HttpUri::createFromString($value);
          $url = $this->urlResolver->__invoke($relativeUrl);
          $value = (string)$url;
        }
        break;

    }

    return $value;
  }

  private function parseDate($value) {
    if (!$value) {
      return null;
    }
    if ($value instanceof \DateTime) {
      return $value;
    }

    $date = null;
    // JSON date (/Date(...)/)
    if (preg_match('@/Date\(([0-9]+)\)/@', $value, $matches)) {
      $date = new \DateTime();
      $date->setTimestamp(((int)$matches[1]) / 1000);
    } else if (is_numeric($value)) {
      $date = new \DateTime();
      $date->setTimestamp($value);
    }

    if ($date === null) {
      try {
        $date = new \DateTime($value);
      } catch (\Exception $e) {}
    }

    if ($date !== null && $this->feed && $this->feed->getTimeZone()) {
      $date->setTimezone($this->feed->getTimeZone());
    }

    return $date;
  }
}
