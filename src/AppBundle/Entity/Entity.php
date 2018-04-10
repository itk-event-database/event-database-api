<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Entity;

abstract class Entity
{
    private $skipImport = false;

    public function setSkipImport($skipImport)
    {
        $this->skipImport = $skipImport;

        return $this;
    }

    public function getSkipImport()
    {
        return $this->skipImport;
    }
}
