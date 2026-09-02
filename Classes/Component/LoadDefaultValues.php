<?php

namespace Rfuehricht\Formhandler\Component;

use Psr\Http\Message\ResponseInterface;

/**
 * This component adds the possibility to load default values.
 */
class LoadDefaultValues extends AbstractComponent
{

    public function process(): array|ResponseInterface
    {
        foreach ($this->settings as $fieldName => $defaultValue) {
            $this->gp[$fieldName] = $this->formUtility->processTypoScriptValue($defaultValue);
        }
        return $this->gp;
    }

}
