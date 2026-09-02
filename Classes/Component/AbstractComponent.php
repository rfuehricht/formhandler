<?php

namespace Rfuehricht\Formhandler\Component;

use Psr\Http\Message\ResponseInterface;
use Rfuehricht\Formhandler\Utility\FormUtility;
use Rfuehricht\Formhandler\Utility\Globals;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\Exception\ContentRenderingException;


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
        protected readonly Globals $globals
    ) {
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


    /**
     * Renders content object to get settings value if applicable
     *
     * @return string
     */
    protected function processTypoScriptValue(array|string $setting): string
    {
        if (is_string($setting)) {
            return $setting;
        } elseif (isset($setting['_typoScriptNodeValue'])) {
            /** @var ContentObjectRenderer $contentObjectRenderer */
            $contentObjectRenderer = $this->request->getAttribute('currentContentObject');
            $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $value = $setting['_typoScriptNodeValue'];
            $typoScriptArray = $typoScriptService->convertPlainArrayToTypoScriptArray($setting);
            try {
                if ($contentObjectRenderer->getContentObject($value)) {
                    return $contentObjectRenderer->cObjGetSingle($value, $typoScriptArray);
                } else {
                    return $contentObjectRenderer->stdWrap($value, $typoScriptArray);
                }
            } catch (ContentRenderingException $e) {
                return '';
            }
        }
        return '';
    }

}
