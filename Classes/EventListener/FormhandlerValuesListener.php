<?php

namespace Rfuehricht\Formhandler\EventListener;

use Rfuehricht\Configloader\Utility\ConfigurationUtility;
use Rfuehricht\Formhandler\Utility\Globals;
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
        protected ConfigurationUtility $configurationUtility
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

            /** @var Globals $globals */
            $globals = GeneralUtility::makeInstance(Globals::class);
            $values = $GLOBALS['TYPO3_REQUEST']->getParsedBody() ?? [];
            $values = $values['tx_formhandler_form'] ?? [];

            if ($formValuesPrefix) {
                $values = $values[$formValuesPrefix] ?? [];
            }
            $globals->setRandomId($values['randomId'] ?? '');
            $globals->setFormValuesPrefix($formValuesPrefix);

            $values = array_merge($globals->getSession()->get('values') ?? [], $values);
            $value = $values[$key] ?? '';
            $event->setValue($value);
        }
    }
}
