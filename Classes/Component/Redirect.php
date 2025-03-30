<?php

namespace Rfuehricht\Formhandler\Component;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

/**
 * Sample implementation of a Finisher Class used by Formhandler redirecting to another page.
 * This class needs a parameter "redirect_page" to be set in TS.
 *
 * Sample configuration:
 *
 * <code>
 * finishers.4.class = redirect
 * finishers.4.config.redirectPage = 65
 * </code>
 *

 */
class Redirect extends AbstractComponent
{

    private ?UriBuilder $uriBuilder = null;

    public function process(): array|ResponseInterface
    {

        if (!isset($this->settings['redirectPage'])) {
            return $this->gp;
        }
        $this->uriBuilder->setRequest($this->request);
        $this->globals->getSession()->reset();

        return new RedirectResponse(
            uri: $this->uriBuilder->reset()->setTargetPageUid($this->settings['redirectPage'])->buildFrontendUri(),
            status: $this->settings['statusCode'] ?? 302,
            headers: $this->settings['additionalHeaders'] ?? []

        );
    }

    public function injectUriBuilder(UriBuilder $uriBuilder): void
    {
        $this->uriBuilder = $uriBuilder;
    }

}
