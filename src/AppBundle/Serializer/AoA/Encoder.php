<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Serializer\AoA;

use Symfony\Component\Serializer\Encoder\JsonEncoder;

class Encoder extends JsonEncoder
{
    public function supportsEncoding($format)
    {
        return 'jsonaoa' === $format;
    }

    public function supportsDecoding($format)
    {
        return false;
    }

    public function encode($data, $format, array $context = [])
    {
        $context['json_encode_options'] = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT;

        return parent::encode($data, $format, $context);
    }
}
