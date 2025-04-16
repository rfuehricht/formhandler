<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;
class EqualsField extends AbstractErrorCheck
{

    public function check(string $fieldName, array $values, array $settings = []): array|string
    {
        $checkFailed = '';

        if (isset($values[$fieldName]) && strlen(trim($values[$fieldName])) > 0) {
            $comparisonValue = $values[$settings['field']];

            if (strcmp($comparisonValue, $values[$fieldName]) !== 0) {
                $checkFailed = $this->getCheckFailed();
            }
        }
        return $checkFailed;
    }

}
