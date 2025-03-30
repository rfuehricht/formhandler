<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;

/**
 * Validates that a specified field is a string and shorter a specified count of characters
 *
 */
class MaxLength extends AbstractErrorCheck
{

    public function check(string $fieldName, array $values, array $settings = []): string
    {
        $checkFailed = '';

        $max = intval($settings['value']);
        if (isset($values[$fieldName]) &&
            mb_strlen(trim($values[$fieldName]), 'UTF-8') > 0 &&
            $max > 0 &&
            mb_strlen(trim($values[$fieldName]), 'UTF-8') > $max
        ) {

            $checkFailed = $this->getCheckFailed();
        }
        return $checkFailed;
    }

}
