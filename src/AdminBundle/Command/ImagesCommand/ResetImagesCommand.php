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
use AppBundle\Entity\Event;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetImagesCommand extends ImagesCommand
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
        $this->setName('admin:images:reset');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->imageGenerator->reset(Event::class, $output);
    }
}
