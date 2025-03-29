<?php

namespace Rfuehricht\Formhandler\Utility;

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
        $items = $config['items'];
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
