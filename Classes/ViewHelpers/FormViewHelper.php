<?php

namespace Rfuehricht\Formhandler\ViewHelpers;

use Rfuehricht\Formhandler\Utility\Globals;

class FormViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper
{

    protected Globals $globals;

    public function injectGlobals(Globals $globals): void
    {
        $this->globals = $globals;
    }

    public function render(): string
    {
        if (!$this->hasArgument('actionUri') && !$this->hasArgument('action')) {
            $this->arguments['action'] = 'form';
        }
        if (!$this->hasArgument('method')) {
            $this->arguments['method'] = 'post';
        }
        if (!$this->hasArgument('enctype')) {
            foreach ($this->globals->getValidations() as $validations) {
                foreach ($validations as $validation) {
                    if (str_starts_with($validation['check'], 'file')) {

                        $this->tag->addAttribute('enctype', 'multipart/form-data');
                    }
                }
            }
        }
        $this->tag->addAttribute('id', 'form-' . $this->globals->getRandomId());
        return parent::render();
    }

    protected function renderHiddenIdentityField(mixed $object, ?string $name): string
    {
        $hiddenFields = parent::renderHiddenIdentityField($object, $name);
        $name = $this->prefixFieldName('randomId');
        if ($this->globals->getFormValuesPrefix()) {
            $name = str_replace('[randomId]', '[' . $this->globals->getFormValuesPrefix() . '][randomId]', $name);
        }
        $hiddenFields .= LF . '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($this->globals->getRandomId()) . '" />' . LF;
        return $hiddenFields;
    }
}
