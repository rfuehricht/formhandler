<?php

namespace Rfuehricht\Formhandler\Utility;


use DateTime;
use Exception;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * A class providing helper functions for Formhandler
 *
 * @author    Reinhard Führicht <rf@typoheads.at>
 */
class FormUtility implements SingletonInterface
{

    public function __construct(
        private readonly Globals $globals
    )
    {

    }

    public function getFilesArray(): array
    {
        $uploadedFiles = $_FILES['tx_formhandler_form'] ?? $_FILES ?? [];
        $formValuesPrefix = $this->globals->getFormValuesPrefix();
        if ($formValuesPrefix) {
            foreach ($uploadedFiles as &$info) {
                if (isset($info[$formValuesPrefix])) {
                    $info = $info[$formValuesPrefix];
                }
            }
            unset($info);
        }

        return $uploadedFiles;
    }

    /**
     * Convert a shorthand byte value from a PHP configuration directive to an integer value
     *
     * Copied from http://www.php.net/manual/de/faq.using.php#78405
     *
     * @param string $value
     *
     * @return int
     */
    public function convertBytes(string $value): int
    {
        if (is_numeric($value)) {
            return $value;
        } else {
            $value_length = strlen($value);

            $qty = substr($value, 0, $value_length - 1);
            $unit = strtolower(substr($value, $value_length - 1));
            if (!is_numeric($qty)) {
                $qty = intval(substr($value, 0, $value_length - 2));
                $unit = strtolower(substr($value, $value_length - 2));
            }
            $qty = intval($qty);

            switch ($unit) {
                case 'k':
                case 'kb':
                    $qty *= 1024;
                    break;
                case 'm':
                case 'mb':
                    $qty *= 1048576;
                    break;
                case 'g':
                case 'gb':
                    $qty *= 1073741824;
                    break;
            }
            return $qty;
        }
    }

    /**
     * Converts a date to a UNIX timestamp.
     *
     * @param string $date
     * @param string $format
     * @return int The timestamp
     */
    public function dateToTimestamp(string $date, string $format = 'Y-m-d'): int
    {
        $timestamp = 0;
        if (strlen(trim($date)) > 0) {

            $dateObj = DateTime::createFromFormat($format, $date);
            if ($dateObj) {
                $timestamp = $dateObj->getTimestamp();
            }
        }
        return $timestamp;
    }

    /**
     * Performs search and replace settings defined in TypoScript.
     *
     * Example:
     *
     * <code>
     * plugin.Tx_Formhandler.settings.files.search = ä,ö,ü
     * plugin.Tx_Formhandler.settings.files.replace = ae,oe,ue
     * </code>
     *
     * @param string $fileName The file name
     *
     * @return string The replaced file name
     *
     **/
    public function doFileNameReplace(string $fileName): string
    {

        $settings = $this->globals->getSettings();

        //Default: Replace spaces with underscores
        $search = [' ', '%20'];
        $replace = ['_'];

        $usePregReplace = boolval($settings['files']['usePregReplace'] ?? false);
        if ($usePregReplace === true) {
            $search = ['/ /', '/%20/'];
        }

        //The settings "search" and "replace" are comma separated lists
        if (isset($settings['files']['search'])) {
            $search = $settings['files']['search'];
            if (!is_array($search)) {
                $search = explode(',', $search);
            }
        }
        if (isset($settings['files']['replace'])) {
            $replace = $settings['files']['replace'];
            if (!is_array($replace)) {
                $replace = explode(',', $replace);
            }
        }
        if ($usePregReplace === true) {
            $fileName = preg_replace($search, $replace, $fileName);
        } else {
            $fileName = str_replace($search, $replace, $fileName);
        }
        return $fileName;
    }

    /**
     * Searches for upload folder settings in TypoScript setup.
     * If no settings is found, the default upload folder is set.
     *
     * Here is an example:
     * <code>
     * plugin.Tx_Formhandler.settings.files.uploadFolder = uploads/formhandler/tmp
     * </code>
     *
     * The default upload folder is: '/uploads/formhandler/'
     *
     * @return string
     */
    public function getUploadFolder(string $fieldName = ''): string
    {

        //set default upload folder
        $uploadFolder = '/uploads/formhandler/tmp/';

        //if temp upload folder set in TypoScript, take that setting
        $settings = $this->globals->getSettings();
        if (strlen($fieldName) > 0 && isset($settings['files']['uploadFolder'][$fieldName])) {
            $uploadFolder = $settings['files']['uploadFolder'][$fieldName];
        } elseif (isset($settings['files.']['uploadFolder']['default'])) {
            $uploadFolder = $settings['files']['uploadFolder']['default'];
        } elseif (isset($settings['files']['uploadFolder'])) {
            $uploadFolder = $settings['files']['uploadFolder'];
        }

        $uploadFolder = rtrim(Environment::getPublicPath(), '/') . $this->sanitizePath($uploadFolder);

        if (!is_dir($uploadFolder)) {
            $this->throwException($uploadFolder . ' directory does not exist');
        }
        return $uploadFolder;
    }

    /**
     * Ensures that a given path has a / as first and last character.
     * This method only appends a / to the end of the path, if no filename is in path.
     *
     * Examples:
     *
     * uploads/temp                --> /uploads/temp/
     * uploads/temp/file.ext    --> /uploads/temp/file.ext
     *
     * @param string $path
     *
     * @return string Sanitized path
     */
    static public function sanitizePath(string $path): string
    {
        if (!str_starts_with($path, '/') && substr($path, 1, 2) !== ':/') {
            $path = '/' . $path;
        }
        if (substr($path, (strlen($path) - 1)) !== '/' && !str_contains($path, '.')) {
            $path = $path . '/';
        }
        while (str_contains($path, '//')) {
            $path = str_replace('//', '/', $path);
        }
        return $path;
    }

    /**
     * Manages the exception throwing
     *
     * @param string $key Key in language file
     * @return void
     * @throws Exception
     */
    public function throwException(string $key): void
    {
        $message = $this->getExceptionMessage($key);
        if (strlen($message) == 0) {
            throw new Exception($key);
        } else {
            if (func_num_args() > 1) {
                $args = func_get_args();
                array_shift($args);
                $message = vsprintf($message, $args);
            }
            throw new Exception($message);
        }
    }

    /**
     * Returns an exception message according to given key
     *
     * @param string $key The key in translation file
     * @return string
     */
    public function getExceptionMessage(string $key): string
    {
        return trim(LocalizationUtility::translate('LLL:EXT:formhandler/Resources/Private/Language/locallang_exceptions.xlf:' . $key));
    }

    public function translate(string $key, array $arguments = []): string
    {
        $languageFiles = $this->globals->getSettings()['languageFile'] ?? [];
        if (!is_array($languageFiles) && strlen(trim($languageFiles)) > 0) {
            $languageFiles = [$languageFiles];
        }
        $value = '';
        foreach ($languageFiles as $languageFile) {
            if ($value === '' || $value === null) {
                if (!str_starts_with($languageFile, 'LLL:')) {
                    $languageFile = 'LLL:' . $languageFile;
                }
                $value = LocalizationUtility::translate(
                    key: $languageFile . ':' . $key,
                    arguments: $arguments
                );
                if (!$value) {
                    $value = LocalizationUtility::translate(
                        key: $languageFile . ':' . strtolower($key),
                        arguments: $arguments
                    );
                }
            }
        }
        return $value ?? '';
    }

    /**
     * Adds needed prefix to class name if not set in TS
     *
     * @param string $className
     * @return string
     */
    public function prepareClassName(string $className): string
    {
        $className = trim($className);
        $className = ltrim($className, '\\');
        $className = ucfirst($className);
        if ($className === 'Default' ||
            $className === 'Rfuehricht\\Formhandler\\Validator\\Default') {
            $className = 'Rfuehricht\\Formhandler\\Validator\\DefaultValidator';
        }
        if (!str_contains($className, '\\')) {
            $className = 'Rfuehricht\\Formhandler\\Component\\' . $className;
        }
        if (substr_count($className, '\\') === 1) {
            $className = 'Rfuehricht\\Formhandler\\' . $className;
        }

        return ltrim($className, '\\');
    }


    public function generateRandomId(): string
    {
        return md5(GeneralUtility::makeInstance(Random::class)->generateRandomBytes(16));
    }

    /**
     * Return a hash value to send by email as an auth code.
     *
     * @param array $row The submitted form data
     * @return string The auth code
     */
    public function generateAuthCode(array $row): string
    {
        /** @var HashService $hashService */
        $hashService = GeneralUtility::makeInstance(HashService::class);
        return $hashService->hmac(serialize($row), 'formhandler');
    }

    /**
     * Removes file with given index from uploaded files for given field.
     * If no index is given, all files of this field are removed.
     *
     * @param string $fieldName
     * @param int|null $index
     * @return void
     */
    public function removeFile(string $fieldName, ?int $index): void
    {
        $sessionFiles = $this->globals->getSession()->get('files');

        if (is_array($sessionFiles) && isset($sessionFiles[$fieldName])) {
            if ($index !== null) {
                $fullPath = $sessionFiles[$fieldName][$index]['uploaded_path'] .
                    $sessionFiles[$fieldName][$index]['uploaded_name'];
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                if (isset($sessionFiles[$fieldName][$index])) {
                    unset($sessionFiles[$fieldName][$index]);
                }
            } else {
                foreach ($sessionFiles[$fieldName] as $index => $file) {
                    $fullPath = $file['uploaded_path'] .
                        $file['uploaded_name'];
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                    if (isset($sessionFiles[$fieldName][$index])) {
                        unset($sessionFiles[$fieldName][$index]);
                    }
                }
            }
        }

        $this->globals->getSession()->set('files', $sessionFiles);
    }
}
