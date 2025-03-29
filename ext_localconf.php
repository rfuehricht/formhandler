<?php

use Rfuehricht\Formhandler\Ajax\RemoveFile;
use Rfuehricht\Formhandler\Controller\FormController;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::class::configurePlugin(
    'Formhandler',
    'Form',
    [FormController::class => 'form'],
    [FormController::class => 'form'],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

$iconRegistry->registerIcon(
    'formhandler-icon',
    BitmapIconProvider::class,
    ['source' => 'EXT:formhandler/ext_icon.svg']
);

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['formhandler-remove-file'] = RemoveFile::class . '::process';
