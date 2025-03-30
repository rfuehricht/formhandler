<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;

/**
 * Validates that an uploaded file has a minimum file size
 *
 */
class FileMinSize extends AbstractErrorCheck
{

    public function check(string $fieldName, array $values, array $settings = []): string
    {
        $checkFailed = '';
        $minSize = $settings['minSize'];
        if (is_numeric($minSize)) {
            $minSize = intval($minSize);
        } else {
            $minSize = $this->formUtility->convertBytes($minSize);
        }
        $files = $this->formUtility->getFilesArray();
        if (!is_array($files['name'][$fieldName])) {
            $files['name'][$fieldName] = [$files['name'][$fieldName]];
        }
        if (empty($files['name'][$fieldName][0])) {
            $files['name'][$fieldName] = [];
        }

        if (count($files['name'][$fieldName]) > 0 && $minSize) {
            if (!is_array($files['size'][$fieldName])) {
                $files['size'][$fieldName] = [$files['size'][$fieldName]];
            }
            foreach ($files['size'][$fieldName] as $size) {
                if ($size < $minSize) {
                    unset($files);
                    $checkFailed = $this->getCheckFailed();
                }
            }
        }
        return $checkFailed;
    }

}
