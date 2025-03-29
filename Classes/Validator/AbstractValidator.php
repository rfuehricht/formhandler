<?php

namespace Rfuehricht\Formhandler\Validator;


use Psr\Http\Message\ResponseInterface;
use Rfuehricht\Formhandler\Component\AbstractComponent;

/**
 * Abstract class for validators for Formhandler
 */
abstract class AbstractValidator extends AbstractComponent
{


    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }


    /**
     * Validates the submitted values using given settings
     *
     * @param array $errors Reference to the errors array to store the errors occurred
     * @return bool
     */
    abstract public function validate(array &$errors): bool;

    public function process(): array|ResponseInterface
    {
        return $this->gp;
    }

}
