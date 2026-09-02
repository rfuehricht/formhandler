<?php

namespace Rfuehricht\Formhandler\EventListener;

use Rfuehricht\Formhandler\Utility\FormUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\TypoScript\AST\Event\EvaluateModifierFunctionEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Replaces `formhandlerValues(...)` strings with values from the current form.
 *
 */
#[AsEventListener(
    identifier: 'formhandler/evaluate-formhandler-values-function',
    event: EvaluateModifierFunctionEvent::class
)]
final class FormhandlerValuesListener
{
    public function __construct(
        protected FormUtility $formUtility
    ) {
    }

    public function __invoke(EvaluateModifierFunctionEvent $event): void
    {
        if ($event->getFunctionName() === 'formhandlerValues') {
            $functionArgument = $event->getFunctionArgument();
            $functionArgument = trim($functionArgument, "'");

            $parts = GeneralUtility::trimExplode(':', $functionArgument, true);

            $formValuesPrefix = '';
            $key = $functionArgument;
            if (count($parts) > 1) {
                $formValuesPrefix = $parts[0];
                $key = $parts[1];
            }

            $values = $this->formUtility->getFormhandlerValues($formValuesPrefix);

            if (str_contains($key, '|')) {
                $keys = GeneralUtility::trimExplode('|', $key, true);
                $value = $this->formUtility->getValueFromRecursiveData($keys, $values);
            } else {
                $value = $values[$key] ?? '';
            }


            $event->setValue($value);
        }
    }
}
