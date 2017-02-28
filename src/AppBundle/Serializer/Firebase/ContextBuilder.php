<?php

namespace AppBundle\Serializer\Firebase;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use AppBundle\Entity\Occurrence;
use Symfony\Component\HttpFoundation\Request;

class ContextBuilder implements SerializerContextBuilderInterface {
  private $decorated;

  public function __construct(SerializerContextBuilderInterface $decorated)
  {
    $this->decorated = $decorated;
  }

  public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null) : array
  {
    $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
    if (!$normalization) {
      return $context;
    }
    $subject = $request->attributes->get('data');

    $info = pathinfo($request->getPathInfo());

    if (isset($info['extension']) && $info['extension'] === 'firebase') {
      header('Content-type: text/plain'); echo var_export([get_class($subject)], true); //die(__FILE__.':'.__LINE__.':'.__METHOD__);
      if ($subject instanceof Occurrence) {
        //$context['groups'] = array_filter($context['groups'], function ($group) { return $group !== 'event_read'; });
      }
    }

    return $context;
  }
}
