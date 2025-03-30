<?php

namespace Rfuehricht\Formhandler\Component;

use Psr\Http\Message\ResponseInterface;

/**
 * This PreProcessor adds the possibility to load default values.
 * Values fot the first step are loaded to $gp values of other steps are stored
 * to the session.
 *
 * Example configuration:
 *
 * <code>
 * preProcessors.1.class = LoadDefaultValues
 * preProcessors.1.config.1.contact_via.defaultValue = email
 * preProcessors.1.config.2.[field1].defaultValue = 0
 * preProcessors.1.config.2.[field2].defaultValue {
 *       data = date : U
 *       strftime = %A, %e. %B %Y
 * }
 * preProcessors.1.config.2.[field3].defaultValue < plugin.tx_exampleplugin
 * <code>
 *
 * may copy the TS to the default validator settings to avoid redundancy
 * Example:
 *
 * validators.1.config.fieldConf.[field].errorCheck.1.notDefaultValue
 * validators.1.config.fieldConf.[field].errorCheck.1.notDefaultValue.defaultValue < plugin.tx_formhandler.settings.preProcessors.1.config.1.[field].defaultValue
 *
 */
class LoadDefaultValues extends AbstractComponent
{

    public function process(): array|ResponseInterface
    {
        foreach ($this->settings as $fieldName => $defaultValue) {
            $this->gp[$fieldName] = $defaultValue;
        }
        return $this->gp;
    }

}
