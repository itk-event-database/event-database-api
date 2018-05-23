<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service\FeedReader;

interface Controller
{
    /**
     * @param array $data
     *
     * @return
     */
    public function createEvent(array $data);

    /**
     * @param $value
     * @param $name
     *
     * @return
     */
    public function convertValue($value, $name);
}
