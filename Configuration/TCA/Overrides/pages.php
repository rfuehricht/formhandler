<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::registerPageTSConfigFile(
    'theme',
    'Configuration/TsConfig/setup.tsconfig',
    'Theme',
);
