<?php

namespace AppBundle\Serializer\Firebase;

use Symfony\Component\Serializer\Encoder\JsonEncoder;

class Encoder extends JsonEncoder
{

    public function supportsEncoding($format)
    {
        return 'firebase' === $format;
    }

    public function supportsDecoding($format)
    {
        return false;
    }

    public function encode($data, $format, array $context = array())
    {
        $context['json_encode_options'] = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT;
        return parent::encode($data, $format, $context);
    }
}
