<?php

namespace Rfuehricht\Formhandler\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class FileRemoveButtonViewHelper extends AbstractViewHelper
{

    protected $escapeOutput = false;

    protected $escapeChildren = false;

    public function render(): string
    {
        $fileInfo = $this->arguments['file'];
        $textContent = trim($this->arguments['value'] ?? $this->renderChildren());
        return '<button
                    data-formhandler-action="remove"
                    data-field="' . $fileInfo['field'] . '"
                    data-index="' . $fileInfo['index'] . '">' . $textContent . '
                 </button>';

    }

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'file',
            'array',
            'Formhandler file info',
            true
        );
        $this->registerArgument(
            'value',
            'string',
            'Text content of the button'
        );
    }
}
