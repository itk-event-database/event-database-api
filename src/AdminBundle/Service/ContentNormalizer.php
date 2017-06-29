<?php

namespace AdminBundle\Service;

/**
 *
 */
class ContentNormalizer implements ContentNormalizerInterface
{
  /**
   * @var \HTMLPurifier
   */
    private $purifier;

  /**
   * @var array
   */
    private $configuration;

  /**
   * @param \HTMLPurifier $purifier
   * @param array $configuration
   */
    public function __construct(\HTMLPurifier $purifier, array $configuration = null)
    {
        $this->purifier = $purifier;
        $this->configuration = $configuration;
    }

  /**
   * @param string $content
   * @return string
   */
    public function normalize($content)
    {
        if (empty($content)) {
            return $content;
        }

        $content = $this->purifier->purify($content);

        if (isset($this->configuration['strip_tags'])) {
            $content = strip_tags($content);
        }

        if (isset($this->configuration['max_length'])) {
            $length = intval($this->configuration['max_length']);
            if ($length > 0) {
                $content = $this->truncate($content, $length, true);
            }
        }

        return $content;
    }

  /**
   * Truncate a text.
   *
   * @see https://github.com/twigphp/Twig-extensions/blob/master/lib/Twig/Extensions/Extension/Text.php
   *
   * @param $value
   * @param $length
   * @param bool $preserve
   * @param string $separator
   *
   * @return string
   */
    private function truncate($value, $length, $preserve = false, $separator = '…')
    {
        $charset = 'UTF8';
        if (mb_strlen($value, $charset) > $length) {
            if ($preserve) {
                // If breakpoint is on the last word, return the value without separator.
                if (false === ($breakpoint = mb_strpos($value, ' ', $length, $charset))) {
                    return $value;
                }
                $length = $breakpoint;
            }
            return rtrim(mb_substr($value, 0, $length, $charset)) . $separator;
        }
        return $value;
    }
}
