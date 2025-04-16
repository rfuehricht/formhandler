<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;

class PregMatch extends AbstractErrorCheck
{

    public function check(string $fieldName, array $values, array $settings = []): array|string
    {
        $checkFailed = '';

        if (isset($values[$fieldName]) && strlen(trim($values[$fieldName])) > 0) {
            $regex = $settings['value'];
            if ($regex && !preg_match($regex, $values[$fieldName])) {
                $checkFailed = $this->getCheckFailed();
            }
        }
        return $checkFailed;
    }

}
