<?php

namespace Rfuehricht\Formhandler\ExpressionLanguage;

use Rfuehricht\Formhandler\Utility\FormUtility;
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

                $formUtility = GeneralUtility::makeInstance(FormUtility::class, $globals);
                return $formUtility->getFormhandlerValues($formValuesPrefix);
            }
        );
    }
}
