<?php

namespace Rfuehricht\Formhandler\Component;
/*

/**
 * Load GET/POST parameters passed from another page.
 *
 */

use Psr\Http\Message\ResponseInterface;

class LoadGetPost extends AbstractComponent
{
    public function process(): array|ResponseInterface
    {
        $loadedGP = $this->loadGP();
        $this->gp = array_merge($loadedGP, $this->gp ?? []);
        return $this->gp;
    }

    /**
     * Loads the GET/POST parameters into the internal storage $this->gp
     *
     * @return array The loaded parameters
     */
    protected function loadGP(): array
    {
        $gp = array_merge($this->request->getQueryParams(), $this->request->getParsedBody()) ?? [];
        $formValuesPrefix = $this->globals->getFormValuesPrefix();
        if ($formValuesPrefix) {
            $gp = $gp[$formValuesPrefix] ?? [];
        }
        if (!is_array($gp)) {
            $gp = [];
        }
        return $gp;
    }

}
