<?php

namespace Rfuehricht\Formhandler\Utility;

use Rfuehricht\Formhandler\Session\AbstractSession;
use Rfuehricht\Formhandler\Session\PHP;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * A helper class for Formhandler to store global values
 */
class Globals implements SingletonInterface
{

    protected string $formValuesPrefix = '';
    protected string $randomId = '';

    protected ?AbstractSession $session;

    protected ?ViewInterface $view;

    protected array $settings = [];

    protected array $values = [];

    protected array $errors = [];

    protected array $validations = [];


    public function getSession(): AbstractSession
    {
        if (!isset($this->session)) {
            $this->session = GeneralUtility::makeInstance(PHP::class);
            $this->session->start();
        }
        return $this->session;
    }

    public function setSession(AbstractSession $session): void
    {
        $this->session = $session;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }


    public function getFormValuesPrefix(): string
    {
        return $this->formValuesPrefix;
    }

    public function setFormValuesPrefix(string $formValuesPrefix): void
    {
        $this->formValuesPrefix = $formValuesPrefix;
    }

    public function getRandomId(): string
    {
        return $this->randomId;
    }

    public function setRandomId(string $randomId): void
    {
        $this->randomId = $randomId;
    }

    public function getView(): ?ViewInterface
    {
        return $this->view;
    }

    public function setView(ViewInterface $view): void
    {
        $this->view = $view;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $values): void
    {
        $this->values = $values;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    public function getValidations(): array
    {
        return $this->validations;
    }

    public function setValidations(array $validations): void
    {
        $this->validations = $validations;
    }


}
