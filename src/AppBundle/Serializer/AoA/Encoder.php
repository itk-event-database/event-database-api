<?php

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
}
