<?php

namespace Rfuehricht\Formhandler\Validator;


use Rfuehricht\Formhandler\Validator\ErrorCheck\AbstractErrorCheck;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A default validator for Formhandler providing basic validations.
 */
class DefaultValidator extends AbstractValidator
{

    protected array $restrictErrorChecks = [];
    protected array $disableErrorCheckFields = [];

    protected bool $fileChecksOnly = false;

    /**
     * Validates the submitted values using given settings
     *
     * @param array &$errors
     * @param bool $fileChecksOnly
     * @return boolean
     */
    public function validate(array &$errors, bool $fileChecksOnly = false): bool
    {

        //no config? validation returns TRUE
        if (!is_array($this->settings['fieldConf'])) {
            return true;
        }

        if (isset($this->settings['disableErrorCheckFields'])) {
            $this->disableErrorCheckFields = [];
            if (is_array($this->settings['disableErrorCheckFields'])) {
                foreach ($this->settings['disableErrorCheckFields'] as $disableCheckField => $checks) {
                    $this->disableErrorCheckFields[$disableCheckField] = GeneralUtility::trimExplode(
                        ',',
                        $checks
                    );
                }
            } else {
                $fields = GeneralUtility::trimExplode(',', $this->settings['disableErrorCheckFields']);
                foreach ($fields as $disableCheckField) {
                    $this->disableErrorCheckFields[$disableCheckField] = [];
                }
            }
        }

        if (isset($this->settings['restrictErrorChecks'])) {
            $this->restrictErrorChecks = GeneralUtility::trimExplode(',', $this->settings['restrictErrorChecks']);
        }

        $this->fileChecksOnly = $fileChecksOnly;

        if (!in_array('all', array_keys($this->disableErrorCheckFields))) {
            $errors = $this->validateRecursive($errors, $this->gp, $this->settings['fieldConf']);
        }

        $globalLimit = 0;
        if (isset($this->settings['messageLimit'])) {
            $globalLimit = intval($this->settings['messageLimit']);
        }

        foreach ($errors as $field => $messages) {
            if (isset($this->settings['fieldConf'][$field]['messageLimit'])) {
                $localLimit = intval($this->settings['fieldConf'][$field]['messageLimit']);
                if ($localLimit > 0) {
                    $errors[$field] = array_slice($messages, -$localLimit);
                }
            } elseif ($globalLimit > 0) {
                $errors[$field] = array_slice($messages, -$globalLimit);
            }
        }

        return empty($errors);
    }

    /**
     * Recursively calls the configured errorChecks. It's possible to set up
     * errorChecks for each key in multidimensional arrays:
     *
     * <code title="errorChecks for arrays">
     * <input type="text" name="birthdate[day]"/>
     * <input type="text" name="birthdate[month]"/>
     * <input type="text" name="birthdate[year]"/>
     * <input type="text" name="name"/>
     *
     * validators.1.config.fieldConf {
     *   birthdate {
     *     day.errorCheck {
     *       1 = betweenValue
     *       1.minValue = 1
     *       1.maxValue = 31
     *     }
     *     month.errorCheck {
     *       1 = betweenValue
     *       1.minValue = 1
     *       1.maxValue = 12
     *     }
     *     year.errorCheck {
     *       1 = minValue
     *       1.minValue = 45
     *     }
     *   }
     *   birthdate.errorCheck.1 = maxItems
     *   birthdate.errorCheck.1.value = 3
     *   name.errorCheck.1 = required
     * }
     * </code>
     *
     * @param array $errors
     * @param array $gp
     * @param array $fieldConf
     * @param string|null $rootField
     * @return array The error array
     */
    protected function validateRecursive(array $errors, array $gp, array $fieldConf, string $rootField = null): array
    {

        //foreach configured form field
        foreach ($fieldConf as $fieldName => $fieldSettings) {

            $errorFieldName = ($rootField === null) ? $fieldName : $rootField;

            $tempSettings = $fieldSettings;
            if (is_array($tempSettings) && !isset($tempSettings['_typoScriptNodeValue'])) {
                // Nested field-configurations - do recursion:
                $errors = $this->validateRecursive($errors, (array)($gp[$fieldName] ?? []), $tempSettings, $errorFieldName);
            }

            if (!isset($fieldSettings['errorCheck']) || !is_array($fieldSettings['errorCheck'])) {
                continue;
            }

            $counter = 0;
            $errorChecks = [];

            //set required to first position if set
            foreach ($fieldSettings['errorCheck'] as $checkKey => $check) {
                $check = $check['_typoScriptNodeValue'] ?? trim($check);
                if (!strcmp($check, 'required') || !strcmp($check, 'file_required')) {
                    $errorChecks[$counter]['check'] = $check;
                    unset($fieldSettings['errorCheck'][$checkKey]);
                    $counter++;
                }
            }


            //set other errorChecks
            foreach ($fieldSettings['errorCheck'] as $checkKey => $check) {
                $check = $check['_typoScriptNodeValue'] ?? trim($check);
                $errorChecks[$counter]['check'] = $check;
                if (is_array($fieldSettings['errorCheck'][$checkKey])) {
                    $errorChecks[$counter]['params'] = $fieldSettings['errorCheck'][$checkKey];
                }
                $counter++;
            }

            //foreach error checks
            foreach ($errorChecks as $check) {

                if ($this->fileChecksOnly && !str_starts_with($check['check'], 'file')) {
                    continue;
                }

                //Skip error check if the check is disabled for this field or if all checks are disabled for this field
                if (!empty($this->disableErrorCheckFields) &&
                    in_array($errorFieldName, array_keys($this->disableErrorCheckFields)) &&
                    (
                        in_array($check['check'], $this->disableErrorCheckFields[$errorFieldName]) ||
                        empty($this->disableErrorCheckFields[$errorFieldName])
                    )
                ) {

                    continue;
                }
                $classNameFix = ucfirst($check['check']);
                if (!str_contains($classNameFix, '\\')) {
                    $errorCheckObject = GeneralUtility::makeInstance($this->formUtility->prepareClassName('\\Rfuehricht\\Formhandler\\Validator\\ErrorCheck\\' . $classNameFix));
                } else {
                    //Look for the whole error check name, maybe it is a custom check
                    $errorCheckObject = GeneralUtility::makeInstance($check['check']);
                }

                /** @var AbstractErrorCheck $errorCheckObject */
                if (empty($this->restrictErrorChecks) || in_array($check['check'], $this->restrictErrorChecks)) {

                    $checkFailed = $errorCheckObject->check($fieldName, $gp, $check['params'] ?? []);
                    if (is_array($checkFailed)) {
                        foreach ($checkFailed as $field => $failedCheck) {
                            $errors[$field] = $errors[$field] ?? [];
                            if (!is_array($errors[$field])) {
                                $errors[$field] = [];
                            }
                            $errors[$field][] = $failedCheck;
                        }
                    } else if (strlen($checkFailed) > 0) {
                        $errors[$errorFieldName] = $errors[$errorFieldName] ?? [];
                        if (!is_array($errors[$errorFieldName])) {
                            $errors[$errorFieldName] = [];
                        }
                        $errors[$errorFieldName][] = $checkFailed;
                    }
                }
            }
        }
        return $errors;
    }
}
