<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class TwigExtension.
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

    /**
     * @param $path
     */
    public function includeFile($path)
    {
        include $path;
    }
}
