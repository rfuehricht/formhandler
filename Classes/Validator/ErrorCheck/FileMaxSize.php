<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Validates that an uploaded file has a maximum file size
 *
 */
class FileMaxSize extends AbstractErrorCheck
{

    public function check(string $fieldName, array $values, array $settings = []): string
    {
        $checkFailed = '';
        $maxSize = $settings['maxSize'];
        if (is_numeric($maxSize)) {
            $maxSize = intval($maxSize);
        } else {
            $maxSize = $this->formUtility->convertBytes($maxSize);
        }
        $phpIniUploadMaxFileSize = $this->formUtility->convertBytes(ini_get('upload_max_filesize'));
        if ($maxSize > $phpIniUploadMaxFileSize) {
            $this->formUtility->throwException('error_check_filemaxsize', GeneralUtility::formatSize($maxSize, ' Bytes| KB| MB| GB'), $fieldName, GeneralUtility::formatSize($phpIniUploadMaxFileSize, ' Bytes| KB| MB| GB'));
        }

        $files = $this->formUtility->getFilesArray();
        if (!isset($files['name'][$fieldName])) {
            return '';
        }
        if (!is_array($files['name'][$fieldName])) {
            $files['name'][$fieldName] = [$files['name'][$fieldName]];
        }
        if (strlen($files['name'][$fieldName][0]) > 0 && $maxSize) {

            if (!is_array($files['size'][$fieldName])) {
                $files['size'][$fieldName] = [$files['size'][$fieldName]];
            }
            foreach ($files['size'][$fieldName] as $size) {
                if ($size > $maxSize) {
                    unset($files);
                    $checkFailed = $this->getCheckFailed();
                }
            }
        }
        return $checkFailed;
    }

}
