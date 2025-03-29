<?php

namespace Rfuehricht\Formhandler\ViewHelpers;

use Rfuehricht\Formhandler\Utility\FormUtility;
use Rfuehricht\Formhandler\Utility\Globals;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
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
        $key = $this->arguments['key'];
        $languageFiles = $this->globals->getSettings()['languageFile'] ?? [];
        if (!is_array($languageFiles) && strlen(trim($languageFiles)) > 0) {
            $languageFiles = [$languageFiles];
        }
        $value = '';
        foreach ($languageFiles as $languageFile) {
            if (!str_starts_with($languageFile, 'LLL:')) {
                $languageFile = 'LLL:' . $languageFile;
            }
            $value = LocalizationUtility::translate(
                key: $languageFile . ':' . $key,
                arguments: $this->arguments['arguments'] ?? []);
        }
        return $value;

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
