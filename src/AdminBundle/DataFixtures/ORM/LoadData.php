<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class LoadData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    use ContainerAwareTrait;

    protected $order = 1;
    protected $flush = true;

    /**
     * @var ConsoleOutput */
    private $output;

    /**
     * {@inheritdoc}.
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param $filename
     *
     * @return string
     */
    protected function loadFixture($filename)
    {
        $basepath = $this->container->get('kernel')->locateResource('@AdminBundle/DataFixtures/Data');

        return file_get_contents($basepath.'/'.$filename);
    }

    /**
     * @param $message
     */
    final protected function writeInfo($message)
    {
        if (func_num_args() > 1) {
            $message = call_user_func_array('sprintf', func_get_args());
        }
        // $this->output->writeln('');.
        $this->output->writeln('  <comment>></comment> <info>'.$message.'</info>');
    }

    /**
     * @param $message
     */
    final protected function writeError($message)
    {
        if (func_num_args() > 1) {
            $message = call_user_func_array('sprintf', func_get_args());
        }
        $this->output->writeln('');
        $this->output->writeln('  <comment>></comment> <error>'.$message.'</error>');
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    protected function done(ObjectManager $manager)
    {
        if ($this->flush) {
            $manager->flush();
        }
    }
}
