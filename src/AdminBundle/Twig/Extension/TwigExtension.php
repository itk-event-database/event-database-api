<?php

namespace AdminBundle\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class TwigExtension
 *
 *
 * @package AdminBundle\Twig\Extension
 */
class TwigExtension extends Twig_Extension
{
    public function getName()
    {
        return __FILE__;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('include_file', [$this, 'includeFile'], ['is_safe' => ['all']]),
        ];
    }

    public function includeFile($path) {
        include($path);
    }
}
