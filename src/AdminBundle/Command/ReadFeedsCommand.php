<?php

namespace AdminBundle\Command;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ReadFeedsCommand extends ContainerAwareCommand {
  protected function configure() {
    $this
      ->setName('events:feeds:read')
      ->setDescription('Read event feeds')
      ->addOption('name', null, InputOption::VALUE_REQUIRED, 'The name of the feed')
      ->addOption('id', null, InputOption::VALUE_REQUIRED, 'The ID of the feed');
  }

  private $em;

  protected function execute(InputInterface $input, OutputInterface $output) {
    $container = $this->getContainer();
    $this->em = $container->get('doctrine')->getEntityManager('default');

    $this->authenticate($container);

    $name = $input->getOption('name');
    $id = $input->getOption('id');

    $feeds = $this->getFeeds($id, $name);
    $noOfFeeds = count($feeds);

    if ($noOfFeeds == 0) {
      $output->writeln('No feeds found!');
    } else {
      $output->writeln(sprintf('Reading %s feed%s:', $noOfFeeds, ($noOfFeeds == 1) ? '' : 's'));
    }

    $client = new Client();

    $reader = $container->get('feed_reader');
    $reader->setOutput($output);

    foreach ($feeds as $name => $feed) {
      $user = $feed->getCreatedBy();
      $feedUrl = $this->processUrl($feed->getUrl());

      $output->writeln([
        str_repeat('-', 80),
        'feed id: ' . $feed->getId(),
        'url:     ' . $feedUrl,
        'user:    ' . $user,
        str_repeat('-', 80)
      ]);

      $res = $client->request('GET', $feedUrl);
      if ($res->getStatusCode() === 200) {
        $content = $res->getBody();
        // http://stackoverflow.com/questions/10290849/how-to-remove-multiple-utf-8-bom-sequences-before-doctype
        $bom = pack('H*','EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);

        $reader->read($content, $feed, $user);
      }
    }
  }

  private function authenticate(ContainerInterface $container) {
    $username = $container->getParameter('admin.feed_reader.username');
    $user = $this->em->getRepository('AppBundle:User')->findOneBy(['username' => $username]);
    $password = $container->getParameter('admin.feed_reader.password');
    $firewall = $container->getParameter('admin.feed_reader.firewall');
    $token = new UsernamePasswordToken($user, $password, $firewall);
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
}
