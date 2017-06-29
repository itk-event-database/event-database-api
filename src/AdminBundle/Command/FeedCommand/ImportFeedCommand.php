<?php

namespace AdminBundle\Command\FeedCommand;

use AdminBundle\Entity\Feed;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Yaml\Yaml;

class ImportFeedCommand extends FeedCommand
{

    protected function configure()
    {
        parent::configure();
        $this
        ->setName('events:feed:import')
        ->setDescription('Import feed configuration')
        ->addOption('yes', null, InputOption::VALUE_NONE, 'Answer "yes" to all confirmation questions')
        ->addOption('name', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Feed name to import')
        ->addArgument('filepath', InputArgument::OPTIONAL, 'File to import');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $yes = $input->getOption('yes');
        $names = $input->getOption('name');

        $path = $input->getArgument('filepath');
        if (!is_readable($path)) {
            throw new \Exception('Cannot read file: ' . $path);
        }

        $config = Yaml::parse(file_get_contents($path));

        if (!is_array($config)) {
            throw new \Exception('Invalid format');
        }

        $unknownNames = array_diff($names, array_keys($config));
        if ($unknownNames) {
            foreach ($unknownNames as $name) {
                $this->writeln(sprintf('<comment>No such feed name: %s</comment>', $name));
            }
        }

        $helper = $this->getHelper('question');
        foreach ($config as $name => $spec) {
            if (empty($names) || in_array($name, $names)) {
                $question = new ConfirmationQuestion(sprintf(
                    'Import feed configuration "%s"? [y/N] ',
                    $name
                ), false);
                if ($yes || $helper->ask($input, $output, $question)) {
                        $this->importFeed($name, $spec);
                }
            } else {
                $this->writeln(sprintf('<info>Skipping feed %s</info>', $name));
            }
        }
    }

    private function importFeed(string $name, array $configuration)
    {
        $userRepository = $this->getContainer()->get('doctrine')->getRepository('AppBundle:User');
        $feedRepository = $this->getContainer()->get('doctrine')->getRepository('AdminBundle:Feed');
        $em = $this->getContainer()->get('doctrine')->getManager();

        $feed = $feedRepository->findOneByName($name);
        $user = $userRepository->findOneByUsername($configuration['user']);
        if (!$user) {
            $this->writeln(sprintf('<error>Unknown user: %s</error>', $configuration['user']));
            return;
        }
        unset($configuration['user']);
        if (!$feed) {
            $this->write(sprintf('<info>Creating feed "%s" ...</info>', $name));
            $feed = new Feed();
        } else {
            $this->write(sprintf('<info>Updating feed "%s" ...</info>', $name));
        }
        $feed
        ->setCreatedBy($user)
        ->setUser($user)
        ->setName($name)
        ->setConfiguration($configuration);

        $em->persist($feed);
        $em->flush();
        $this->writeln(' <info>done</info>.');

        $this->writeln(str_repeat('-', 80));
        $this->write('<comment>');
        $this->writeFeedInfo($feed);
        $this->write('</comment>');
        $this->writeln(str_repeat('-', 80));
        $this->writeln('');
    }
}
