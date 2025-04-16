<?php

namespace Rfuehricht\Formhandler\ViewHelpers;

use Rfuehricht\Formhandler\Utility\FormUtility;
use Rfuehricht\Formhandler\Utility\Globals;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class ErrorMessagesViewHelper extends AbstractViewHelper
{

    public function __construct(
        private readonly FormUtility $formUtility,
        private readonly Globals     $globals
    )
    {

    }

    public function render()
    {
        $field = $this->arguments['field'];
        $specificError = $this->arguments['error'];

        $errors = $this->globals->getErrors();
        $validations = $this->globals->getValidations();

        $fieldErrors = $errors[$field] ?? [];
        $fieldValidations = $validations[$field] ?? [];

        $errorMessages = [];
        foreach ($fieldErrors as $fieldError) {
            if ($specificError && $fieldError !== $specificError) {
                continue;
            }
            $value = $this->formUtility->translate('error_' . $field . '_' . $fieldError);
            if (!$value) {
                $value = $this->formUtility->translate('error_default_' . $fieldError);
            }
            if (!$value) {
                $value = $fieldError;
            }

            $checkArguments = [];
            foreach ($fieldValidations as $fieldValidation) {
                if (strtolower($fieldValidation['check']) === strtolower($fieldError)) {
                    $checkArguments = $fieldValidation['options'];
                }
            }
            foreach ($checkArguments as $key => $argumentValue) {
                $value = str_ireplace('{' . $key . '}', $argumentValue, $value);
            }
            $value = str_ireplace('{fieldName}', $field, $value);

            preg_replace_callback('/{LLL:([^}]+)}/', function ($match) {
                DebugUtility::debug($match);
            }, $value);

            $errorMessages[] = $value;
        }

        if ($specificError) {
            return reset($errorMessages);
        }

        return $errorMessages;

    }

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'field',
            'string',
            'The field to get the message for.',
            true
        );
        $this->registerArgument(
            'error',
            'string',
            'Error to get the message for.'
        );
    }
}
