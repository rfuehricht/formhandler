<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;


class FileMaxTotalSize extends AbstractErrorCheck
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
        $size = 0;

        // first we check earlier uploaded files
        $olderFiles = $this->globals->getSession()->get('files');
        foreach ((array)$olderFiles[$fieldName] as $olderFile) {
            $size += intval($olderFile['size']);
        }

        // last we check currently uploaded file
        $files = $this->formUtility->getFilesArray();
        if (!is_array($files['name'][$fieldName])) {
            $files['name'][$fieldName] = [$files['name'][$fieldName]];
        }
        if (strlen($files['name'][$fieldName][0]) > 0 && $maxSize) {
            if (!is_array($files['size'][$fieldName])) {
                $files['size'][$fieldName] = [$files['size'][$fieldName]];
            }
            foreach ($files['size'][$fieldName] as $fileSize) {
                $size += $fileSize;
            }
            if ($size > $maxSize) {
                unset($files);
                $checkFailed = $this->getCheckFailed();
            }
        }
        return $checkFailed;
    }

}
