<?php

namespace Rfuehricht\Formhandler\Validator\ErrorCheck;


/**
 * Abstract class for error checks for Formhandler
 *
 */
abstract class AbstractErrorCheck
{


    /**
     * Sets the suitable string for the checkFailed message parsed in view.
     *
     * @return string If the check failed, the string contains the name of the failed check plus the parameters and values.
     */
    abstract public function check(string $fieldName, array $values): string;

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
