<?php

namespace AdminBundle\Command;

use AdminBundle\Service\FeedReader\Controller;
use AdminBundle\Entity\Feed;
use AppBundle\Entity\Event;

use Guzzle\Http\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ReadFeedsCommand extends ContainerAwareCommand implements Controller {
  protected function configure() {
    $this->setName('events:read:feeds')
      ->setDescription('Read event feeds');
  }

  // app/console generate:doctrine:entity --no-interaction --entity=AdminBundle:Feed --fields="name:string(255) url:string(255) baseUrl:string(255) type:string(50) root:string(50) mapping:text defaults:text lastRead:date" --format=annotation

  private $em;
  private $output;
  private $feed;
  private $tagManager;

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->output = $output;
    $this->em = $this->getContainer()->get('doctrine')->getEntityManager('default');
    $this->tagManager = $this->getContainer()->get('fpn_tag.tag_manager');
    $feeds = $this->getFeeds();

    $client = new Client();

    foreach ($feeds as $name => $feed) {
      $this->feed = $feed;
      $feedUrl = $this->processUrl($feed->getUrl());
      echo str_repeat('-', 80), PHP_EOL;
      echo $feedUrl, PHP_EOL;
      echo str_repeat('-', 80), PHP_EOL;
      $res = $client->request('GET', $feedUrl);
      if ($res->getStatusCode() === 200) {
        $content = $res->getBody();
        // http://stackoverflow.com/questions/10290849/how-to-remove-multiple-utf-8-bom-sequences-before-doctype
        $bom = pack('H*','EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);

        switch ($feed->getType()) {
          case 'json':
            $json = json_decode($content, true);
            $reader = $this->getContainer()->get('feed_reader.json');
            $reader
              ->setController($this)
              ->setFeed($feed);
            $reader->read($json);
            $feed->setLastRead(new \DateTime());
            break;

          case 'xml':
            $xml = new \SimpleXmlElement($content);
            $reader = $this->getContainer()->get('feed_reader.xml');
            $reader
              ->setController($this)
              ->setFeed($feed);
            $reader->read($xml);
            $feed->setLastRead(new \DateTime());
            break;

          default:
            throw new \Exception('Unknown feed type: ' . $feed->getType());
        }
      }
    }
  }

  private function processUrl($url) {
    $twig = $this->getContainer()->get('twig');
    $template = $twig->createTemplate($url);
    return $template->render([]);
  }

  private function getFeeds() {
    $query = $this->em->createQuery('SELECT f FROM AdminBundle:Feed f');
    return $query->getResult();
  }

  private function getFeedEventId($id) {
    return $this->feed->getName() . ' - ' . $id;
  }

  public function createEvent(array $eventData) {
    $id = isset($eventData['id']) ? $eventData['id'] : uniqid();
    unset($eventData['id']);

    $event = $this->getEvent($id);

    $isNew = !$event->getId();

    $event->setValues($eventData, $this->tagManager);
    $event->setFeed($this->feed);
    $this->em->persist($event);
    $this->em->flush();
    $this->tagManager->saveTagging($event);

    $this->output->writeln(sprintf('%s: Event %s: %s (%s)', $this->feed->getName(), ($isNew ? 'created' : 'updated'), $event->getName(), $event->getFeedEventId()));

    return $event;
  }

  private function getEvent($id) {
    $feedEventId = $this->getFeedEventId($id);

    $query = $this->em->createQuery('SELECT e FROM AppBundle:Event e WHERE e.feedEventId = :feedEventId');
    $query->setParameter('feedEventId', $feedEventId);

    $events = $query->getResult();

    if (count($events) === 0) {
      $event = new Event();
      $event->setFeedEventId($feedEventId);
      return $event;
    } else if (count($events) > 1) {
    }
    return $events[0];
  }

  public function convertValue($value, $name) {
    switch ($name) {
      case 'startDate':
      case 'endDate':
        return $this->parseDate($value);
        break;
      case 'url':
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
        break;
    }

    return $value;
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
