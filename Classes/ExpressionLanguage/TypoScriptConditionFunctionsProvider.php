<?php

namespace Rfuehricht\Formhandler\ExpressionLanguage;

use Rfuehricht\Formhandler\Utility\Globals;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Add custom conditions to the TypoScript condition provider.
 */
class TypoScriptConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{


    public function getFunctions(): array
    {
        return [
            $this->getFormValuesFunction()
        ];
    }


    /**
     *
     * @return ExpressionFunction
     */
    protected function getFormValuesFunction(): ExpressionFunction
    {
        return new ExpressionFunction(
            'formhandlerValues',
            static fn() => null, // Not implemented, we only use the evaluator
            static function (array $arguments, string $formValuesPrefix = '') {

                /** @var Globals $globals */
                $globals = GeneralUtility::makeInstance(Globals::class);
                $values = $arguments['request']->getParsedBody() ?? [];
                $values = $values['tx_formhandler_form'] ?? [];
                if ($formValuesPrefix) {
                    $values = $values[$formValuesPrefix] ?? [];
                }
                $globals->setRandomId($values['randomId'] ?? '');
                $globals->setFormValuesPrefix($formValuesPrefix);

                return array_merge($globals->getSession()->get('values') ?? [], $values);

            }
        );
    }
}
