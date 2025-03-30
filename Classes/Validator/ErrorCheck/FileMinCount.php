<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;

/**
 * Abstract class for validators for Formhandler
 *
 */
class FileMinCount extends AbstractErrorCheck
{
    public function check(string $fieldName, array $values, array $settings = []): string
    {
        $checkFailed = '';

        $sessionFiles = $this->globals->getSession()->get('files');
        $currentStep = $this->globals->getSession()->get('currentStep');
        $lastStep = $this->globals->getSession()->get('lastStep');
        $minCount = $settings['minCount'];
        if (is_array($sessionFiles[$fieldName]) &&
            $currentStep > $lastStep
        ) {

            $files = $this->formUtility->getFilesArray();
            if (!is_array($files['name'][$fieldName])) {
                $files['name'][$fieldName] = [$files['name'][$fieldName]];
            }
            if (empty($files['name'][$fieldName][0])) {
                $files['name'][$fieldName] = [];
            }
            if ((count($files['name'][$fieldName]) + count($sessionFiles[$fieldName])) < $minCount) {
                $checkFailed = $this->getCheckFailed();
            }
        }

        return $checkFailed;
    }

}
