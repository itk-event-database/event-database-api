<?php

namespace AdminBundle\Command;

use AdminBundle\Service\FeedReader\Controller;
use AdminBundle\Service\FeedReader\ValueConverter;
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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Yaml\Yaml;

class ReadFeedsCommand extends ContainerAwareCommand implements Controller {
  protected function configure() {
    $this->setName('events:read:feeds')
      ->setDescription('Read event feeds');
  }

  private $em;
  private $output;
  private $feed;
  private $tagManager;
  private $converter;

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->output = $output;
    $container = $this->getContainer();
    $this->authenticate($container);
    $this->em = $container->get('doctrine')->getEntityManager('default');
    $this->tagManager = $container->get('fpn_tag.tag_manager');
    $feeds = $this->getFeeds();

    $client = new Client();

    $imagesPath = $container->getParameter('admin.images_path');
    $baseUrl = $container->getParameter('admin.base_url');

    foreach ($feeds as $name => $feed) {
      $this->feed = $feed;
      $this->converter = new ValueConverter($this->feed, $imagesPath, $baseUrl);
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
            $reader = $container->get('feed_reader.json');
            $reader
              ->setController($this)
              ->setFeed($feed);
            $reader->read($json);
            $feed->setLastRead(new \DateTime());
            break;

          case 'xml':
            $xml = new \SimpleXmlElement($content);
            $reader = $container->get('feed_reader.xml');
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

  private function authenticate(ContainerInterface $container) {
    $username = $container->getParameter('admin.feed_reader.username');
    $password = $container->getParameter('admin.feed_reader.password');
    $firewall = $container->getParameter('admin.feed_reader.firewall');
    $token = new UsernamePasswordToken($username, $password, $firewall);
    $this->getContainer()->get('security.token_storage')->setToken($token);
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

  public function createEvent(array $data) {
    $id = isset($data['id']) ? $data['id'] : uniqid();
    unset($data['id']);

    if (isset($data['image'])) {
      $data['originalImage'] = $data['image'];
      $data['image'] = $this->converter->downloadImage($data['image']);
    }

    $event = $this->getEvent($id);

    $isNew = !$event->getId();

    $event->setValues($data, $this->tagManager);
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
    return $this->converter->convert($value, $name);
  }
}
