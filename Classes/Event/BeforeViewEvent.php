<?php

declare(strict_types=1);

namespace Rfuehricht\Formhandler\Event;

use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * This event triggers right before the view is rendered.
 *
 * Listeners may update view related stuff.
 */
final readonly class BeforeViewEvent
{
    public function __construct(private Request $request)
    {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }


}
