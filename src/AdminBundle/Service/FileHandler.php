<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\TransferStats;
use League\Uri\Http as HttpUri;
use League\Uri\Modifiers\Resolve;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\RequestContext;

class FileHandler
{
    private $logger;
    private $configuration;
    private $baseUrlResolver;
    private $filesPath;
    private $filesUrl;

    /**
     * @var null|GuzzleException
     */
    private $guzzleException;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param array                    $configuration
     */
    public function __construct(LoggerInterface $logger, RequestContext $context, array $configuration)
    {
        $this->logger = $logger;
        $this->configuration = $configuration;
        $this->baseUrlResolver = new Resolve(HttpUri::createFromComponents([
            'scheme' => $context->getScheme(),
            'host' => $context->getHost(),
        ]));
        $this->filesPath = isset($this->configuration['files']['path']) ? rtrim($this->configuration['files']['path'], '/') : null;
        $this->filesUrl = isset($this->configuration['files']['url']) ? rtrim($this->configuration['files']['url'], '/') : null;
    }

    /**
     * Download data from external url and return a local url pointing to the downloaded data.
     *
     * @param string $url
     *                    The url to download data from
     *
     * @return string
     *                The local url
     */
    public function download(string $url)
    {
        if ($this->isLocalUrl($url)) {
            $this->log('info', 'Not downloading from local url: '.$url);

            return $url;
        }

        $this->log('info', 'Downloading from url: '.$url);
        $actualUrl = $url;
        $content = null;

        $this->guzzleException = null;

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
        } catch (GuzzleException $ex) {
            $this->guzzleException = $ex;
            $this->log('error', 'Downloading from '.$url.' failed: '.$ex->getMessage());

            return null;
        }
        if (empty($content)) {
            $this->log('error', 'Downloading from '.$url.' failed. No content');

            return null;
        }

        $filename = md5($actualUrl);
        $info = pathinfo(HttpUri::createFromString($url)->getPath());
        if (!empty($info['extension'])) {
            $filename .= '.'.$info['extension'];
        }
        $path = rtrim($this->filesPath, '/').'/'.$filename;

        $filesystem = new Filesystem();
        $filesystem->dumpFile($path, $content);

        if (empty($info['extension'])) {
            // Try to guess the file extension type and rename it.
            $file = new File($path);
            $extension = $file->guessExtension();
            if ($extension) {
                $filesystem->rename($path, $path.'.'.$extension, true);
                $filename .= '.'.$extension;
            }
        }

        $localUrl = $this->baseUrlResolver->process(HttpUri::createFromString($this->filesUrl.'/'.$filename));

        $this->log('info', 'Data written to file: '.$path.' ('.$url.')');

        return $localUrl->__toString();
    }

    /**
     * Determine if a url is a local url.
     *
     * @param string $url
     *                    The url to check
     *
     * @return bool
     *              True iff the url is local
     */
    public function isLocalUrl(string $url)
    {
        $path = HttpUri::createFromString($url)->getPath();
        $localUrl = $this->baseUrlResolver->process(HttpUri::createFromString($path));
        // Strip query string from url.
        $url = preg_replace('@\?.*$@', '', $url);
        $externalUrl = $this->baseUrlResolver->process(HttpUri::createFromString($url));

        return (string) $localUrl === (string) $externalUrl;
    }

    public function getLocalUrl(string $url)
    {
        return $this->isLocalUrl($url) ? HttpUri::createFromString($url)->getPath() : null;
    }

    public function getLocalPath(string $url)
    {
        return realpath($this->filesPath.'/'.basename($url));
    }

    public function getBaseUrl()
    {
        return $this->baseUrlResolver->process(HttpUri::createFromString());
    }

    public function getBaseDirectory()
    {
        return $this->filesPath;
    }

    public function resolve(string $path, array $query = null)
    {
        return (string) $this->baseUrlResolver->process(HttpUri::createFromComponents([
            'path' => $path,
            'query' => $query ? http_build_query($query) : null,
        ]));
    }

    public function getErrorStatus()
    {
        return $this->guzzleException ? $this->guzzleException->getCode() : null;
    }

    /**
     * @param string $type
     * @param string $message
     */
    private function log(string $type, string $message)
    {
        if ($this->logger) {
            $this->logger->{$type}($message);
        }
    }
}
