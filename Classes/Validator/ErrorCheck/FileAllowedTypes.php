<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Validates that an uploaded file via specified field matches one of the given file types
 *
 */
class FileAllowedTypes extends AbstractErrorCheck
{

    public function check(string $fieldName, array $values, array $settings = []): string
    {
        $checkFailed = '';
        $allowed = $settings['allowedTypes'] ?? '';
        $files = $this->formUtility->getFilesArray();
        $files['name'][$fieldName] = $files['name'][$fieldName] ?? [];
        if (!is_array($files['name'][$fieldName])) {
            $files['name'][$fieldName] = [$files['name'][$fieldName]];
        }
        foreach ($files['name'][$fieldName] as $fileName) {
            if (strlen($fileName) > 0) {
                if ($allowed) {
                    $types = GeneralUtility::trimExplode(',', $allowed);
                    $fileExtension = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
                    if (!in_array($fileExtension, $types)) {
                        unset($files);
                        $checkFailed = $this->getCheckFailed();
                    }
                }
            }
        }
        return $checkFailed;
    }

}
