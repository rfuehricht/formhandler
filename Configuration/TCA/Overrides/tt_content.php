<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

$pluginSignature = ExtensionUtility::registerPlugin(
    'Formhandler',
    'Form',
    'Formhandler',
    'EXT:formhandler/ext_icon.svg'
);


ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;Configuration,pi_flexform',
    $pluginSignature,
    'after:subheader',
);

ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:formhandler/Configuration/Flexforms/Form.xml',
    $pluginSignature
);
