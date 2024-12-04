<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DeleteEventsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:event:delete')
            ->setDescription('Delete all events from feed')
            ->addArgument('feed-id', InputOption::VALUE_REQUIRED, 'Feed ID')
            ->addArgument('username', InputOption::VALUE_REQUIRED, 'Username to run command as');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('AppBundle:User')->findOneByUsername($input->getArgument('username'));

        if (!$user) {
            $output->writeln('<error>Username not found</error>');

            return -1;
        }

        // Fake a user to satisfy "isGranted" calls in doctrine listeners
        $token = new UsernamePasswordToken(
            $user,
            null,
            'main',
            $user->getRoles()
        );
        $this->getContainer()->get('security.token_storage')->setToken($token);

        $feedId = $input->getArgument('feed-id');

        $count = $em->createQueryBuilder()
            ->select('count(e.id)')
            ->from('AppBundle:Event', 'e')
            ->where('e.feed = :feedId')
            ->setParameter('feedId', $feedId)
            ->getQuery()
            ->getSingleScalarResult();

        $iterableResult = $em->createQueryBuilder()
            ->select('e')
            ->from('AppBundle:Event', 'e')
            ->where('e.feed = :feedId')
            ->setParameter('feedId', $feedId)
            ->getQuery()
            ->iterate();

        $progressBar = new ProgressBar($output, $count);
        $progressBar->setFormat('debug');
        $progressBar->start();

        foreach ($iterableResult as $row) {
            $event = $row[0];

            $em->remove($event);
            $em->flush();
            $em->clear();

            $progressBar->advance();
        }

        $progressBar->finish();
    }
}
