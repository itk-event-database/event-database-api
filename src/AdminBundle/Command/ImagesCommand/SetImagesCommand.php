<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Command\ImagesCommand;

use AdminBundle\Service\ImageGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetImagesCommand extends ImagesCommand
{
    /** @var \AdminBundle\Service\ImageGenerator */
    private $imageGenerator;

    public function __construct(ImageGenerator $imageGenerator)
    {
        parent::__construct();
        $this->imageGenerator = $imageGenerator;
    }

    protected function configure()
    {
        $this->setName('admin:images:set')
            ->setDescription('Sett images on entities based on image filters configuration.')
            ->addArgument('className', InputArgument::REQUIRED, 'The entity className to process')
            ->addArgument('ids', InputArgument::OPTIONAL, 'The entity ids (csv)')
            ->addOption('reset', null, InputOption::VALUE_NONE, 'Reset images on entities before setting them.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $className = $input->getArgument('className');
        $entityIds = $input->getArgument('ids');
        if (null !== $entityIds) {
            $entityIds = explode(',', $entityIds);
        }

        if ($input->getOption('reset')) {
            $this->imageGenerator->reset($className, $entityIds, $output);
        }
        $this->imageGenerator->setImagesBatch($className, $entityIds, $output);
    }
}
