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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Yaml\Yaml;

class ReadFeedsCommand extends ContainerAwareCommand implements Controller {
  protected function configure() {
    $this
      ->setName('events:feeds:read')
      ->setDescription('Read event feeds')
      ->addOption('name', null, InputOption::VALUE_REQUIRED, 'The name of the feed')
      ->addOption('id', null, InputOption::VALUE_REQUIRED, 'The ID of the feed');
  }

  private $em;
  private $output;
  private $feed;
  private $tagManager;
  private $converter;
  private $eventFactory;

  protected function execute(InputInterface $input, OutputInterface $output) {
    $name = $input->getOption('name');
    $id = $input->getOption('id');

    $this->output = $output;
    $container = $this->getContainer();
    $this->authenticate($container);
    $this->em = $container->get('doctrine')->getEntityManager('default');
    $this->tagManager = $container->get('fpn_tag.tag_manager');

    $feeds = $this->getFeeds($id, $name);
    $noOfFeeds = count($feeds);

    if ($noOfFeeds == 0) {
      $this->output->writeln('No feeds found!');
    } else {
      $this->output->writeln(sprintf('Reading %s feed%s:', $noOfFeeds, ($noOfFeeds == 1) ? '' : 's'));
    }

    $client = new Client();

    $imagesPath = $container->getParameter('admin.images_path');
    $baseUrl = $container->getParameter('admin.base_url');
    $this->converter = $container->get('value_converter');

    foreach ($feeds as $name => $feed) {
      $this->feed = $feed;
      $this->eventFactory = $container->get('event_factory');
      $this->eventFactory->setFeed($feed);
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

  private function getFeeds($id, $name) {
    $qb = $this->em->createQueryBuilder();
    $qb->select('f')->from('AdminBundle:Feed', 'f');
    if($id) {
      $qb->andWhere('f.id = :identifier')->setParameter('identifier', $id);
    }
    if($name) {
      $qb->andWhere('f.name = :name')->setParameter('name', $name);
    }
    $query = $qb->getQuery();
    return $query->getResult();
  }

  public function createEvent(array $data) {
    $data['feed'] = $this->feed;
    $event = $this->eventFactory->get($data);

    $status = ($event->getUpdatedAt() > $event->getCreatedAt()) ? 'updated' : 'created';
    $this->output->writeln(sprintf('% 8d %s: Event %s: %s (%s)', $this->feed->getId(), $this->feed->getName(), $status, $event->getName(), $event->getFeedEventId()));
  }

  public function convertValue($value, $name) {
    return $this->converter->convert($value, $name);
  }
}
