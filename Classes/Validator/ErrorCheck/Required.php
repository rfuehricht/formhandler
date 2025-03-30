<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;


/**
 * Validates that a specified field is filled out
 *
 */
class Required extends AbstractErrorCheck
{

    public function check(string $fieldName, array $values, array $settings = []): string
    {
        $checkFailed = '';
        if (!isset($values[$fieldName]) || strlen(trim($values[$fieldName])) == 0) {
            $checkFailed = $this->getCheckFailed();
        } elseif (is_array($values[$fieldName]) && empty($values[$fieldName])) {
            $checkFailed = $this->getCheckFailed();
        }
        return $checkFailed;
    }

}
