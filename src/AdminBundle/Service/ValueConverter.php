<?php

namespace AdminBundle\Service;

use AdminBundle\Entity\Feed;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\TransferStats;
use Symfony\Component\Filesystem\Filesystem;
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\Resolve;
use Symfony\Component\HttpFoundation\Tests\RequestContentProxy;

class ValueConverter {
  protected $feed;
  protected $downloadUrlResolver;

  public function setFeed(Feed $feed) {
    $this->feed = $feed;
    $this->downloadUrlResolver = $this->feed->getBaseUrl() ? new Resolve(HttpUri::createFromString($this->feed->getBaseUrl())) : null;
  }

  public function convert($value, $name) {
    switch ($name) {
      case 'startDate':
      case 'endDate':
        return $this->parseDate($value);

      case 'image':
      case 'url':
        if ($this->downloadUrlResolver) {
          $relativeUrl = HttpUri::createFromString($value);
          $url = $this->downloadUrlResolver->__invoke($relativeUrl);
          $value = (string)$url;
        }
        break;

    }

    return $value;
  }

  /**
   * Download image an return its url.
   */
  public function downloadImage(string $url) {
    $filesystem = new Filesystem();
    if (!$filesystem->exists($this->filePath)) {
      $filesystem->mkdir($this->filePath);
    }
    if (!$filesystem->exists($this->filePath)) {
      throw new \Exception('Cannot create download directory ' . $this->filePath);
    }

    $actualUrl = $url;
    $content = null;
    try {
      $client = new Client();
      $content = $client->get($url, [
        'on_stats' => function (TransferStats $stats) use (&$actualUrl) {
          $actualUrl = $stats->getEffectiveUri();
        }
      ])->getBody()->getContents();
    } catch (ClientException $ex) {}
    if (empty($content)) {
      return null;
    }

    $filename = md5($actualUrl);
    $info = pathinfo($actualUrl);
    if (!empty($info['extension'])) {
      $filename .= '.' . $info['extension'];
    }
    $path = rtrim($this->filePath, '/') . '/' . $filename;
    file_put_contents($path, $content);
    // @TODO: Return url to image rather than full file path.
    // @HACK!
    $path = (string)($this->baseUrlResolver)(HttpUri::createFromString(preg_replace('@^.*web/@', '', $path)));
    return $path;
  }

  private function parseDate($value) {
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
