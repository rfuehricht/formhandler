<?php

namespace Rfuehricht\Formhandler\ViewHelpers;

use Rfuehricht\Formhandler\Utility\FormUtility;
use Rfuehricht\Formhandler\Utility\Globals;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class TranslateViewHelper extends AbstractViewHelper
{

    public function __construct(
        private readonly FormUtility $formUtility,
        private readonly Globals     $globals
    )
    {

    }

    public function render()
    {
        return $this->formUtility->translate($this->arguments['key'], $this->arguments['arguments'] ?? []);

    }

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'key',
            'string',
            'The key in translation file',
            true
        );
        $this->registerArgument(
            'arguments',
            'array',
            'Arguments to pass to localization for replacement.'
        );
    }
}
