# EXT:formhandler


Basic configuration looks like this:

```text
plugin.tx_formhandler {

  view {
    partialRootPaths.5 = EXT:theme/Resources/Private/Forms/Partials/
  }

  forms {
    myform {
        name = My Formhandler Form

        view {
            templateRootPaths.10 = EXT:theme/Resources/Private/Forms/MyForm/
            partialRootPaths.10 = EXT:theme/Resources/Private/Forms/MyForm/Partials/
        }

        settings {
            templateFile = Step1

            formValuesPrefix = myform

            languageFile = EXT:theme/Resources/Private/Language/myform.xlf

            preProcessors {

            }
            interceptors {

            }
            validators {

            }
            finishers {

            }
        }
    }
  }
```

All configured forms are available in the plugin settings in a dropdown field. The setting **name** is used as a label.

![Showing the forxform settings of plugin Formhandler. Predefined forms in TypoScript are selectable in a dropdown there.](./Documentation/Images/flexform.png "Flexform Settings")



**view** and **settings** of each form are merged with the "global" TypoScript in **plugin.tx_formhandler**.
This way you can define global settings like partialRootPaths and merge them with form specific settings.

The HTML-Template may look like this:

```html
<html
    data-namespace-typo3-fluid="true"
    xmlns:f="http://typo3.org/ns/fluid/ViewHelpers"
    xmlns:fh="http://typo3.org/ns/Rfuehricht/Formhandler/ViewHelpers">

<div class="form">
    <f:form action="form">
        <f:form.hidden name="{formValuesPrefix}[randomId]" value="{randomId}" />

        <div>
            <f:for each="{validations.{name}}" as="check">
                <f:if condition="{check.check} == 'required'">
                    <f:variable name="isRequired" value="*" />
                </f:if>
            </f:for>
            <label for="{formValuesPrefix}-name">{fh:translate(key: 'label.name')} {isRequired}</label>
            <f:form.textfield id="{formValuesPrefix}-name"
                              name="{formValuesPrefix}[name]"
                              value="{values.name}"
            />
            <f:if condition="{errors.name}">
                <ul>
                    <f:for as="error" each="{fh:errorMessages(field: 'name')}">
                        <li>{error}</li>
                    </f:for>
                </ul>
            </f:if>
        </div>


        <f:form.submit name="{formValuesPrefix}[{submit.nextStep}]"
                       value="{fh:translate(key: 'label.submit')}"
        />
    </f:form>
</div>
```

That is all for a very simple and basic form.

Have a look at the [documentation](./Documentation/Index.md) for more details about available

* [settings](./Documentation/Settings.md)
* [components](./Documentation/Components.md)
* [error checks](./Documentation/Validation.md)
* [view helpers](./Documentation/ViewHelpers.md)
* [templating](./Documentation/Templating.md)

and much more.

