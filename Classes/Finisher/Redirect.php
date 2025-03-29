<?php

namespace Rfuehricht\Formhandler\Finisher;
/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

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
 * finishers.4.class = Finisher\Redirect
 * finishers.4.config.redirectPage = 65
 * </code>
 *

 */
class Redirect extends AbstractFinisher
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
