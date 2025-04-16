<?php

namespace Rfuehricht\Formhandler\Component;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This component adds the possibility to load default values.
 */
class LoadDefaultValues extends AbstractComponent
{

    public function process(): array|ResponseInterface
    {
        /** @var ContentObjectRenderer $contentObjectRenderer */
        $contentObjectRenderer = $this->request->getAttribute('currentContentObject');
        foreach ($this->settings as $fieldName => $defaultValue) {
            if (is_string($defaultValue)) {
                $this->gp[$fieldName] = $defaultValue;
            } elseif (isset($defaultValue['_typoScriptNodeValue'])) {
                $this->gp[$fieldName] = $contentObjectRenderer->cObjGetSingle($defaultValue['_typoScriptNodeValue'], $defaultValue);
            }
        }
        return $this->gp;
    }

}
