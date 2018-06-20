<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Command\FilesCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadFilesCommand extends FilesCommand
{
    protected function configure()
    {
        $this
        ->setName('events:files:download')
        ->setDescription('Download files to local storage')
        ->addArgument('className', InputArgument::REQUIRED, 'The entity className to process')
        ->addArgument('ids', InputArgument::REQUIRED, 'The entity ids (csv). Use "all" to process all entities.')
        ->addArgument('fields', InputArgument::REQUIRED, 'The entity fields to process (csv)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $className = $input->getArgument('className');
        $ids = preg_split('/\s*,\s*/', $input->getArgument('ids'), null, PREG_SPLIT_NO_EMPTY);
        $fields = preg_split('/\s*,\s*/', $input->getArgument('fields'), null, PREG_SPLIT_NO_EMPTY);

        $service = $this->getContainer()->get('download_files');
        $service->setOutput($output);
        $service->process($className, $ids, $fields);
    }
}
