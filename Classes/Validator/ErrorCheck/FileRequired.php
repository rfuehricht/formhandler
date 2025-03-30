<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;

/**
 * Validates that a file gets uploaded via specified upload field
 *
 */
class FileRequired extends AbstractErrorCheck
{

    public function check(string $fieldName, array $values, array $settings = []): string
    {
        $checkFailed = '';
        $sessionFiles = $this->globals->getSession()->get('files');
        $found = false;
        $files = $this->formUtility->getFilesArray();

        $files['name'][$fieldName] = $files['name'][$fieldName] ?? [];
        if (!is_array($files['name'][$fieldName])) {
            $files['name'][$fieldName] = [$files['name'][$fieldName]];
        }
        if (is_array($files['name'][$fieldName]) && !empty($files['name'][$fieldName][0])) {
            $found = true;
        }
        if (!is_array($sessionFiles[$fieldName])) {
            $sessionFiles[$fieldName] = [];
        }
        if (!$found && count($sessionFiles[$fieldName]) === 0) {
            $checkFailed = $this->getCheckFailed();
        }
        return $checkFailed;
    }

}
