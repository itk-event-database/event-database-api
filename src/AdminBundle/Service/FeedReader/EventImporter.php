<?php

namespace AdminBundle\Service\FeedReader;

use AdminBundle\Entity\Feed;
use AdminBundle\Factory\EventFactory;
use AdminBundle\Factory\PlaceFactory;
use AppBundle\Entity\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\TransferStats;
use League\Uri\Modifiers\Resolve;
use League\Uri\Schemes\Http as HttpUri;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Filesystem\Filesystem;

class EventImporter {
  protected $eventFactory;
  protected $placeFactory;
  protected $configuration;
  protected $baseUrlResolver;
  protected $imagesPath;
  protected $imagesUrl;

  protected $feed;
  protected $user;
  protected $logger;

  public function __construct(EventFactory $eventFactory, PlaceFactory $placeFactory, array $configuration) {
    $this->eventFactory = $eventFactory;
    $this->placeFactory = $placeFactory;
    $this->configuration = $configuration;
    $this->baseUrlResolver = isset($this->configuration['base_url']) ? new Resolve(HttpUri::createFromString($this->configuration['base_url'])) : null;
    $this->imagesPath = isset($this->configuration['images']['path']) ? rtrim($this->configuration['images']['path'], '/') : null;
    $this->imagesUrl = isset($this->configuration['images']['url']) ? rtrim($this->configuration['images']['url'], '/') : null;
  }

  public function setFeed(Feed $feed) {
    $this->feed = $feed;

    return $this;
  }

  public function setUser(User $user) {
    $this->user = $user;
    if ($this->placeFactory) {
      $this->placeFactory->setUser($user);
    }

    return $this;
  }

  public function setLogger(Logger $logger) {
    $this->logger = $logger;

    return $this;
  }

  public function import(array $data) {
    if ($this->imagesPath && isset($data['image'])) {
      try {
        $localFile = $this->downloadFile($data['image'], $this->imagesPath);
        $localUrl = $this->baseUrlResolver->__invoke(HttpUri::createFromString($this->imagesUrl . '/' . $localFile));
        if ($this->logger) {
          $this->logger->info('Image downloaded: ' . $data['image'] . ' → ' . $localUrl);
        }
        $data['original_image'] = $data['image'];
        $data['image'] = $localUrl;
      } catch (\Exception $ex) {
        // @TODO
        throw $ex;
      }
    }

    $event = $this->eventFactory->get($data);

    return $event;
  }

  /**
   * Download file and return its local path.
   */
  private function downloadFile(string $url, string $basePath) {
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
    $path = rtrim($basePath, '/') . '/' . $filename;

    $filesystem = new Filesystem();
    $filesystem->dumpFile($path, $content);

    return $filename;
  }
}