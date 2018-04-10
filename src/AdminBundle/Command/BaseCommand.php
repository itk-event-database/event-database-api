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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * @var OutputInterface
     */
    protected $output;

    protected $verbose;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->verbose = (bool) $input->getOption('verbose');
    }

    protected function writeln($messages)
    {
        $this->write($messages, true);
    }

    protected function write($messages, $newline = false)
    {
        if ($this->output) {
            $this->output->write($messages, $newline);
        }
    }

    protected function info($messages, $newline = true)
    {
        if ($this->verbose) {
            if (!is_array($messages)) {
                $messages = [$messages];
            }
            $messages = array_map(function ($message) {
                return '<info>'.$message.'</info>';
            }, $messages);
            $this->write($messages, $newline);
        }
    }

    protected function warning($messages, $newline = true)
    {
        if ($this->verbose) {
            if (!is_array($messages)) {
                $messages = [$messages];
            }
            $messages = array_map(function ($message) {
                return '<error>'.$message.'</error>';
            }, $messages);
            $this->write($messages, $newline);
        }
    }
}
