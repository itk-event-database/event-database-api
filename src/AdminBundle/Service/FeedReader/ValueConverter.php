<?php

namespace AdminBundle\Service\FeedReader;

use AdminBundle\Entity\Feed;
use Symfony\Component\Filesystem\Filesystem;
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\Resolve;
use Symfony\Component\HttpFoundation\Tests\RequestContentProxy;

class ValueConverter {
  protected $feed;
  protected $filePath;
  protected $downloadUrlResolver;
  protected $baseUrlResolver;

  public function __construct(Feed $feed, string $filePath, string $baseUrl) {
    $this->feed = $feed;
    $this->filePath = $filePath;
    $this->baseUrlResolver = new Resolve(HttpUri::createFromString($baseUrl));
    $this->downloadUrlResolver = $this->feed->getBaseUrl() ? new Resolve(HttpUri::createFromString($this->feed->getBaseUrl())) : null;
  }

  public function convert($value, $name) {
    switch ($name) {
      case 'startDate':
      case 'endDate':
        return $this->parseDate($value);
        break;

      case 'image':
      case 'url':
        if ($this->downloadUrlResolver) {
          $relativeUrl = HttpUri::createFromString($value);
          $url = ($this->downloadUrlResolver)($relativeUrl);
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
    $uri = HttpUri::createFromString($url);
    $normalizedPath = $uri->path
      ->withoutLeadingSlash()
      ->withoutTrailingSlash()
      ->withoutDotSegments();

    $info = pathinfo($normalizedPath);
    $filename = md5($url);
    if (!empty($info['extension'])) {
      $filename .= '.' . $info['extension'];
    }
    $filesystem = new Filesystem();
    if (!$filesystem->exists($this->filePath)) {
      $filesystem->mkdir($this->filePath);
    }
    if (!$filesystem->exists($this->filePath)) {
      throw new \Exception('Cannot create download directory ' . $this->filePath);
    }
    $path = rtrim($this->filePath, '/') . '/' . $filename;
    // @TODO: Use Guzzle or something for downloading image.
    $content = @file_get_contents($url);
    if (empty($content)) {
      return $content;
    }
    file_put_contents($path, $content);
    // @TODO: Return url to image rather than full file path.
    // @HACK!
    $path = (string)($this->baseUrlResolver)(HttpUri::createFromString(preg_replace('@^.*web/@', '', $path)));
    return $path;
  }

  private function parseDate($value) {
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

    return $date;
  }
}
