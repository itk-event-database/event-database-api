<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Service;

interface ContentNormalizerInterface
{
    /**
     * Normalize html content.
     *
     * @param string $content
     *                        The html string
     *
     * @return string
     *                Normalized and cleaned html
     */
    public function normalize($content);
}
