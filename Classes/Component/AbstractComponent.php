<?php

namespace Rfuehricht\Formhandler\Component;

use Psr\Http\Message\ResponseInterface;
use Rfuehricht\Formhandler\Utility\FormUtility;
use Rfuehricht\Formhandler\Utility\Globals;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;


/**
 * Abstract component class for any usable Formhandler component.
 * This class extends the abstract class and adds some useful variables and methods.
 *
 * @abstract
 */
abstract class AbstractComponent
{

    /**
     * The GET/POST parameters
     *
     * @access protected
     * @var array
     */
    protected array $gp;

    /**
     * Settings
     *
     * @access protected
     * @var array
     */
    protected array $settings;
    protected ?RequestInterface $request = null;

    public function __construct(
        protected readonly FormUtility $formUtility,
        protected readonly Globals     $globals
    )
    {

    }

    /**
     * Initialize the class variables
     *
     * @param array $gp GET and POST variable array
     * @param array $settings TypoScript configuration for the component (component.1.config.*)
     * @param RequestInterface $request
     * @return void
     */
    public function init(array $gp, array $settings, RequestInterface $request): void
    {
        $this->gp = $gp;
        $this->request = $request;
        $this->settings = $settings;
    }

    abstract public function process(): array|ResponseInterface;

}
