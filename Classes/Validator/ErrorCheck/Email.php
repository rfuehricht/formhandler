<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class Email extends AbstractErrorCheck
{

    public function check(string $fieldName, array $values, array $settings = []): string
    {
        if (isset($values[$fieldName]) && strlen(trim($values[$fieldName])) > 0) {
            if (!GeneralUtility::validEmail(trim($values[$fieldName]))) {
                return $this->getCheckFailed();
            }
        }
        return '';
    }

}
