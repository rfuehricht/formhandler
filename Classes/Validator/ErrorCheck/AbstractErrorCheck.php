<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;


use Rfuehricht\Formhandler\Utility\FormUtility;
use Rfuehricht\Formhandler\Utility\Globals;

/**
 * Abstract class for error checks for Formhandler
 *
 */
abstract class AbstractErrorCheck
{

    public function __construct(
        protected readonly FormUtility $formUtility,
        protected readonly Globals     $globals
    )
    {

    }

    /**
     * Sets the suitable string for the checkFailed message parsed in view.
     *
     * @return string If the check failed, the string contains the name of the failed check plus the parameters and values.
     */
    abstract public function check(string $fieldName, array $values, array $settings = []): string;

    /**
     * Sets the suitable string for the checkFailed message parsed in view.
     *
     * @return string The check failed string
     */
    protected function getCheckFailed(): string
    {
        $parts = explode('\\', get_class($this));
        return lcfirst(array_pop($parts));
    }


}
