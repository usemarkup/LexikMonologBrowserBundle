<?php

namespace Lexik\Bundle\MonologBrowserBundle\Formatter;

use Monolog\Formatter\NormalizerFormatter as BaseFormatter;

class NormalizerFormatter extends BaseFormatter
{
    protected function normalize($data, $depth = 0)
    {
        $data = parent::normalize($data, $depth);

        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
            }
        }

        return $data;
    }
}
