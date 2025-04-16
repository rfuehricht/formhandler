<?php

namespace Rfuehricht\Formhandler\ViewHelpers;

use Rfuehricht\Formhandler\Utility\FormUtility;
use Rfuehricht\Formhandler\Utility\Globals;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class GetViewHelper extends AbstractViewHelper
{

    public function __construct(
        private readonly FormUtility $formUtility,
        private readonly Globals     $globals
    )
    {

    }

    public function render()
    {
        $key = $this->arguments['key'];
        return match ($key) {
            'values' => $this->globals->getValues(),
            'formValuesPrefix' => $this->globals->getFormValuesPrefix(),
            'errors' => $this->globals->getErrors(),
            'validations' => $this->globals->getValidations(),
            default => '',
        };
    }

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'key',
            'string',
            'The key to fetch',
            true
        );
    }
}
