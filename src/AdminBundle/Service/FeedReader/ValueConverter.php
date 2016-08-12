<?php

namespace AdminBundle\Service\FeedReader;

use AdminBundle\Entity\Feed;
use Symfony\Component\Filesystem\Filesystem;

class ValueConverter {
  protected $feed;
  protected $filePath;

  public function __construct(Feed $feed, string $filePath) {
    $this->feed = $feed;
    $this->filePath = $filePath;
  }

  public function convert($value, $name) {
    switch ($name) {
      case 'startDate':
      case 'endDate':
        return $this->parseDate($value);
        break;
      case 'image':
      case 'url':
        if (!preg_match('@^[a-z]+://@', $value)) {
          $baseUrl = $this->feed->getBaseUrl();
          if ($baseUrl) {
            $parts = parse_url($baseUrl);
            if (strpos($value, '/') === 0) {
              $parts['path'] = $value;
            } else {
              $parts['path'] = rtrim($parts['path'], '/') . '/' . $value;
            }
            $value = $this->unparse_url($parts);
          }
        }
        break;
    }

    return $value;
  }

  /**
   * Download image an return its url.
   */
  public function downloadImage(string $url) {
    $parts = parse_url($url);
    $info = pathinfo($parts['path']);
    $filename = md5($url);
    if (!empty($info['extension'])) {
      $filename .= '.' . $info['extension'];
    }
    $filesystem = new Filesystem();
    if (!$filesystem->exists($this->filePath)) {
      $filesystem->mkdir($this->filePath);
    }
    $path = $this->filePath . '/' . $filename;
    // @TODO: Use Guzzle or something for downloading image.
    $content = @file_get_contents($url);
    if (empty($content)) {
      return $content;
    }
    file_put_contents($path, $content);
    // @TODO: Return url to image rather than full file path.
    return $path;
  }

  // http://php.net/manual/en/function.parse-url.php#106731
  private function unparse_url($parsed_url) {
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
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
