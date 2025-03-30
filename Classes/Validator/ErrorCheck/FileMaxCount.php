<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;

/**
 * Validates that up to x files get uploaded via the specified upload field.
 *
 */
class FileMaxCount extends AbstractErrorCheck
{

    public function check(string $fieldName, array $values, array $settings = []): string
    {
        $checkFailed = '';

        $sessionFiles = $this->globals->getSession()->get('files');
        $globalSettings = $this->globals->getSession()->get('settings');
        $currentStep = intval($this->globals->getSession()->get('currentStep'));
        $lastStep = intval($this->globals->getSession()->get('lastStep'));
        $maxCount = intval($settings['maxCount']);

        $uploadedFilesWithSameNameAction = $globalSettings['uploadedFilesWithSameName'] ?? 'ignore';

        $files = $this->formUtility->getFilesArray();
        if (is_array($sessionFiles[$fieldName]) &&
            count($sessionFiles[$fieldName]) >= $maxCount &&
            $currentStep === $lastStep
        ) {

            $found = false;


            if (isset($info['name'][$fieldName])) {
                if (!is_array($info['name'][$fieldName])) {
                    $info['name'][$fieldName] = [$info['name'][$fieldName]];
                }
                if (strlen($info['name'][$fieldName][0]) > 0) {
                    $found = true;
                }
            }
            if ($found) {
                foreach ($files['name'][$fieldName] as $newFileName) {

                    $exists = false;
                    foreach ($sessionFiles[$fieldName] as $fileInfo) {
                        if ($fileInfo['name'] === $newFileName) {
                            $exists = true;
                        }
                    }
                    if (!$exists) {
                        $checkFailed = $this->getCheckFailed();
                    } elseif ($uploadedFilesWithSameNameAction === 'append') {
                        $checkFailed = $this->getCheckFailed();
                    }
                }
            }
        } else {
            if (!is_array($sessionFiles[$fieldName])) {
                $sessionFiles[$fieldName] = [];
            }
            foreach ($files as $info) {
                if (!is_array($info['name'][$fieldName])) {
                    $info['name'][$fieldName] = [$info['name'][$fieldName]];
                }
                if (strlen($info['name'][$fieldName][0]) > 0 && count($info['name'][$fieldName]) + count($sessionFiles[$fieldName]) > $maxCount) {
                    $checkFailed = $this->getCheckFailed();
                }
            }
        }
        return $checkFailed;
    }

}
