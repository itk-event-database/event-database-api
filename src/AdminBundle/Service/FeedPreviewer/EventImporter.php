<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service\FeedPreviewer;

use AdminBundle\Service\FeedReader\EventImporter as BaseEventImporter;

class EventImporter extends BaseEventImporter
{
    public function __construct()
    {
    }

    /**
     * @param array $data
     *
     * @return \AppBundle\Entity\Event|object
     */
    public function import(array $data)
    {
        return $data;
    }
}
