<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service;

interface TagNormalizerInterface
{
    /**
     * @param array $names
     *
     * @return
     */
    public function normalize(array $names);
}
