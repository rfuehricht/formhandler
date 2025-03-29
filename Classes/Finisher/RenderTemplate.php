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
use TYPO3\CMS\Core\Http\HtmlResponse;

/**
 * A finisher showing the content of a Fluid template.
 *
 * A sample configuration looks like this:
 * <code>
 * finishers.3.class = Finisher\RenderTemplate
 * finishers.3.config.templateFile = Success
 * </code>
 *

 */
class RenderTemplate extends AbstractFinisher
{

    /**
     * The main method called by the controller
     *
     */
    public function process(): array|ResponseInterface
    {

        //read template file
        $templateFile = $this->settings['templateFile'] ?? null;
        if ($templateFile) {
            $this->globals->getSession()->set('finished', 1);

            $view = $this->globals->getView();
            $view->assignMultiple([
                'values' => $this->gp
            ]);
            return new HtmlResponse($view->render($templateFile));
        }

        return $this->gp;
    }

}
