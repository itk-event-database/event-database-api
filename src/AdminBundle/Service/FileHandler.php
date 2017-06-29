<?php

namespace AdminBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\TransferStats;
use League\Uri\Modifiers\Resolve;
use League\Uri\Schemes\Http as HttpUri;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 *
 */
class FileHandler {
  private $logger;
  private $configuration;
  private $baseUrlResolver;
  private $filesPath;
  private $filesUrl;

  /**
   * @param \Psr\Log\LoggerInterface $logger
   * @param array $configuration
   */
  public function __construct(LoggerInterface $logger, array $configuration) {
    $this->logger = $logger;
    $this->configuration = $configuration;
    $this->baseUrlResolver = isset($this->configuration['base_url']) ? new Resolve(HttpUri::createFromString($this->configuration['base_url'])) : NULL;
    $this->filesPath = isset($this->configuration['files']['path']) ? rtrim($this->configuration['files']['path'], '/') : NULL;
    $this->filesUrl = isset($this->configuration['files']['url']) ? rtrim($this->configuration['files']['url'], '/') : NULL;
  }

  /**
   * Download data from external url and return a local url pointing to the downloaded data.
   *
   * @param string $url
   *   The url to download data from.
   *
   * @return string
   *   The local url.
   */
  public function download(string $url) {
    if ($this->isLocalUrl($url)) {
      $this->log('info', 'Not downloading from local url: ' . $url);
      return $url;
    }

    $this->log('info', 'Downloading from url: ' . $url);
    $actualUrl = $url;
    $content = NULL;
    try {
      $client = new Client();
      $content = $client->get($url, [
        'on_stats' => function (TransferStats $stats) use (&$actualUrl) {
          $actualUrl = $stats->getEffectiveUri();
        },
        // Pretend to be a real browser.
        'headers' => [
          'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
        ],
      ])->getBody()->getContents();
    }
    catch (ClientException $ex) {
      $this->log('error', 'Downloading from ' . $url . ' failed: ' . $ex->getMessage());
      return NULL;
    }
    if (empty($content)) {
      $this->log('error', 'Downloading from ' . $url . ' failed. No content');
      return NULL;
    }

    $filename = md5($actualUrl);
    $info = pathinfo(HttpUri::createFromString($url)->getPath());
    if (!empty($info['extension'])) {
      $filename .= '.' . $info['extension'];
    }
    $path = rtrim($this->filesPath, '/') . '/' . $filename;

    $filesystem = new Filesystem();
    $filesystem->dumpFile($path, $content);

    if (empty($info['extension'])) {
      // Try to guess the file extension type and rename it.
      $file = new File($path);
      $extension = $file->guessExtension();
      if ($extension) {
        $filesystem->rename($path, $path . '.' . $extension, TRUE);
        $filename .= '.' . $extension;
      }
    }

    $localUrl = $this->baseUrlResolver->__invoke(HttpUri::createFromString($this->filesUrl . '/' . $filename));

    $this->log('info', 'Data written to file: ' . $path . ' (' . $url . ')');
    return $localUrl->__toString();
  }

  /**
   * Determine if a url is a local url.
   *
   * @param string $url
   *   The url to check.
   *
   * @return bool
   *   True iff the url is local.
   */
  public function isLocalUrl(string $url) {
    $path = HttpUri::createFromString($url)->getPath();
    $localUrl = $this->baseUrlResolver->__invoke(HttpUri::createFromString($path));
    $externalUrl = $this->baseUrlResolver->__invoke(HttpUri::createFromString($url));

    return $localUrl == $externalUrl;
  }

  public function getLocalPath(string $url) {
    return realpath($this->filesPath . '/' . basename($url));
  }

  public function getBaseUrl() {
    return $this->baseUrlResolver->__invoke(HttpUri::createFromString());
  }

  public function getBaseDirectory() {
    return $this->filesPath;
  }

  /**
   * @param string $type
   * @param string $message
   */
  private function log(string $type, string $message) {
    if ($this->logger) {
      $this->logger->{$type}($message);
    }
  }

}
