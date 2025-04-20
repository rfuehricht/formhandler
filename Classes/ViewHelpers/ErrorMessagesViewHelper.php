<?php

namespace Rfuehricht\Formhandler\ViewHelpers;

use Rfuehricht\Formhandler\Utility\FormUtility;
use Rfuehricht\Formhandler\Utility\Globals;
use TYPO3Fluid\Fluid\Core\Variables\ScopedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class ErrorMessagesViewHelper extends AbstractViewHelper
{

    /**
     * @var bool
     */
    protected $escapeOutput = false;


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

            $value = preg_replace_callback('/{LLL:([^}]+)}/', function ($match) {
                $langKey = $match[1] ?? '';
                if ($langKey) {
                    return $this->formUtility->translate($langKey);
                }
                return '';
            }, $value);

            $errorMessages[] = $value;
        }

        if ($specificError) {
            $errorMessages = reset($errorMessages);
        }

        if ($errorMessages) {
            $variableProvider = new ScopedVariableProvider($this->renderingContext->getVariableProvider(), new StandardVariableProvider([$this->arguments['as'] => $errorMessages]));
            $this->renderingContext->setVariableProvider($variableProvider);
            $output = (string)$this->renderChildren();
            $this->renderingContext->setVariableProvider($variableProvider->getGlobalVariableProvider());
        } else {
            $output = '';
        }
        return $output;
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
            'as',
            'string',
            'The variable name for the errors.',
            defaultValue: 'errors'
        );
        $this->registerArgument(
            'error',
            'string',
            'Error to get the message for.'
        );
    }
}
