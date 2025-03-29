<?php

namespace Rfuehricht\Formhandler\Utility;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

use DateTime;
use Exception;
use TYPO3\CMS\Core\Core\Environment;
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

    /**
     * Adds needed prefix to class name if not set in TS
     *
     * @param string $className
     * @return string
     */
    public function prepareClassName(string $className, $prefix = ''): string
    {
        $className = trim($className);
        $className = ltrim($className, '\\');
        $className = ucfirst($className);
        if ($className === 'Default' ||
            $className === 'Rfuehricht\\Formhandler\\Validator\\Default') {
            $className = 'Rfuehricht\\Formhandler\\Validator\\DefaultValidator';
        }
        if (!str_contains($className, '\\')) {
            if ($prefix) {
                $className = $prefix . '\\' . $className;
            }
            $className = 'Rfuehricht\\Formhandler\\' . $className;
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

}
