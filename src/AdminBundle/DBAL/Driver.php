<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\DBAL;

use Doctrine\DBAL\Driver\PDOMySql\Driver as BaseDriver;

/**
 * Class Driver.
 */
class Driver extends BaseDriver
{
    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return new Platform();
    }
}
