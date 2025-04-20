<?php

namespace Rfuehricht\Formhandler\ViewHelpers;

use Rfuehricht\Formhandler\Utility\Globals;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

final class FileRemoveButtonViewHelper extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    private Globals $globals;

    public function injectGlobals(Globals $globals): void
    {
        $this->globals = $globals;
    }

    public function render(): string
    {
        $name = $this->getName();
        $this->registerFieldNameForFormTokenGeneration($name);

        $this->tag->addAttribute('type', 'submit');
        $this->tag->addAttribute('value', $this->getValueAttribute());
        $this->tag->addAttribute('name', $name);

        return $this->tag->render();
    }

    protected function getName(): string
    {
        $name = 'submit-remove-' . $this->arguments['field'];
        if (isset($this->arguments['index'])) {
            $name .= '-' . $this->arguments['index'];
        }
        if ($this->globals->getFormValuesPrefix()) {
            $name = $this->globals->getFormValuesPrefix() . '[' . $name . ']';
        }
        return $this->prefixFieldName($name);
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        unset($this->argumentDefinitions['name']);
        $this->registerArgument(
            'field',
            'string',
            'The name of the upload field',
            true
        );
        $this->registerArgument(
            'index',
            'integer',
            'The index of the file to remove'
        );
    }


}
