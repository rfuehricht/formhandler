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


}
