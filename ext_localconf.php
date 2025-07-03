<?php

use Rfuehricht\Formhandler\Controller\FormController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::class::configurePlugin(
    'Formhandler',
    'Form',
    [FormController::class => 'form'],
    [FormController::class => 'form'],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
