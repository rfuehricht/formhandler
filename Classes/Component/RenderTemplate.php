<?php

namespace Rfuehricht\Formhandler\Component;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;

/**
 * A finisher showing the content of a Fluid template.
 *
 * A sample configuration looks like this:
 * <code>
 * finishers.3.class = renderTemplate
 * finishers.3.config.templateFile = Success
 * </code>
 *

 */
class RenderTemplate extends AbstractComponent
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
                'values' => $this->gp,
                'config' => $this->settings
            ]);
            return new HtmlResponse($view->render($templateFile));
        }

        return $this->gp;
    }

}
