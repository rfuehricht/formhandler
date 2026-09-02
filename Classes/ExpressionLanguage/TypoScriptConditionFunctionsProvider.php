<?php

namespace Rfuehricht\Formhandler\ExpressionLanguage;

use Rfuehricht\Formhandler\Utility\FormUtility;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * Add custom conditions to the TypoScript condition provider.
 */
class TypoScriptConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{

    public function __construct(
        protected FormUtility $formUtility
    ) {
    }


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
                return $this->formUtility->getFormhandlerValues($formValuesPrefix);
            }
        );
    }
}
