<?php

namespace Rfuehricht\Formhandler\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class TcaUtility
{

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Adds the available predefined forms to a TCA select field.
     *
     * @param array $config
     *
     * @return array
     */
    public function getPredefinedForms(array $config): array
    {
        $request = $GLOBALS['TYPO3_REQUEST'];
        $params = $request->getQueryParams()['edit']['tt_content'] ?? [];
        $paramsKeys = array_keys($params);
        $firstKey = reset($paramsKeys) ?? '';
        $firstValue = reset($params) ?? '';
        if ($firstValue === 'new') {
            $pid = $firstKey;
        } else {
            $contentId = $firstKey;
            if (!$contentId) {
                return $config;
            }
            $contentRecord = BackendUtility::getRecord('tt_content', $contentId) ?? [];
            $pid = $contentRecord['pid'] ?? null;
            if (!$pid) {
                return $config;
            }
        }

        $request = $request
            ->withQueryParams(['id' => $pid]);


        $items = $config['items'];
        $this->configurationManager->setRequest($request);
        $setup = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        if (isset($setup['plugin.']['tx_formhandler.']['forms.'])) {
            foreach ($setup['plugin.']['tx_formhandler.']['forms.'] as $key => $predefinedForm) {
                $name = $value = rtrim($key, '.');
                if (isset($predefinedForm['name'])) {
                    $name = $predefinedForm['name'];
                    if (str_starts_with($name, 'LLL:')) {
                        $name = LocalizationUtility::translate($name);
                    }
                }

                $items[] = ['label' => $name, 'value' => $value];
            }
        }

        $config['items'] = $items;
        return $config;
    }
}
