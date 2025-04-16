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

        if (!isset($sessionFiles[$fieldName]) || !is_array($sessionFiles[$fieldName])) {
            $sessionFiles[$fieldName] = [];
        }

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
            if (isset($files['name'])) {
                if (!isset($files['name'][$fieldName]) || !is_array($files['name'][$fieldName])) {
                    $files['name'][$fieldName] = [];
                }
                if (count($files['name'][$fieldName]) + count($sessionFiles[$fieldName]) > $maxCount) {
                    $checkFailed = $this->getCheckFailed();
                }
            }
        }
        return $checkFailed;
    }

}
