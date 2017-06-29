<?php

namespace AdminBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 *
 */
abstract class LoadData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    use ContainerAwareTrait;

  /**
   * @param $filename
   * @return string
   */
    protected function loadFixture($filename)
    {
        $basepath = $this->container->get('kernel')->locateResource('@AdminBundle/DataFixtures/Data');

        return file_get_contents($basepath . '/' . $filename);
    }

  /**
   * @var ConsoleOutput $output */
    private $output = null;

  /**
   * @param $message
   */
    final protected function writeInfo($message)
    {
        if (func_num_args() > 1) {
            $message = call_user_func_array('sprintf', func_get_args());
        }
        // $this->output->writeln('');.
        $this->output->writeln('  <comment>></comment> <info>' . $message . '</info>');
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
        $this->output->writeln('  <comment>></comment> <error>' . $message . '</error>');
    }

    protected $order = 1;
    protected $flush = true;

  /**
   * @param \Doctrine\Common\Persistence\ObjectManager $manager
   */
    protected function done(ObjectManager $manager)
    {
        if ($this->flush) {
            $manager->flush();
        }
    }

  /**
   * {@inheritDoc}.
   */
    public function getOrder()
    {
        return $this->order;
    }
}
