# Formhandler - ViewHelpers

Formhandler provides some view helpers to make templating easier.

<!-- TOC -->
* [translate](#translate)
* [fileRemoveButton](#fileremovebutton)
* [get](#get)
  * [Available keys](#available-keys)
<!-- TOC -->

## translate

Loop through all configured files in `languageFile` and search for a translation of given `key`.

Arguments:

| Argument  | Type    | Description                                          |
|-----------|---------|------------------------------------------------------|
| key       | string  | Key in language file                                 |
| arguments | array   | Optional arguments to replace in translated message  |


Example:

```html
<html
    data-namespace-typo3-fluid="true"
    xmlns:fh="http://typo3.org/ns/Rfuehricht/Formhandler/ViewHelpers">
<fh:translate
    key="required_message"
    arguments="{0: 'value to replace'}" />

{fh:translate(key: 'required_message')}

</html>
```

## fileRemoveButton

Renders a button to remove an uploaded file.

Arguments:

| Argument | Type   | Description                                                                 |
|----------|--------|-----------------------------------------------------------------------------|
| file     | object | The file to remove                                                          |
| value    | string | Value of the button. Either as argument or text content of the view helper. |


Example:

```html
<html
    data-namespace-typo3-fluid="true"
    xmlns:fh="http://typo3.org/ns/Rfuehricht/Formhandler/ViewHelpers">

<f:if condition="{files.file}">
    <ul>
        <f:for as="file" each="{files.file}" key="index">
            <li>{file.name} ({file.size -> f:format.bytes()})
                <fh:fileRemoveButton file="{file}">X</fh:fileRemoveButton>
            </li>
        </f:for>
    </ul>
</f:if>

</html>
```

## get

Get global Formhandler attributes or values.
All these values are passed as variables to the FLUID templates, but you may need these data outside the form or in a partial.
This makes it possible to access the data without having to pass it to the partial.

Arguments:

| Argument | Type   | Description                                                                 |
|----------|--------|-----------------------------------------------------------------------------|
| key     | string | The key of the data to get.                                                 |

### Available keys

| Key              | Description                                    |
|------------------|------------------------------------------------|
| values           | The currently stored form values of all steps. |
| formValuesPrefix | The configured form values prefix.             |
| errors           | The errors of the current step.                |
| validations      | The validation settings of the current step.   |

Example:

```html
<f:variable name="validations" value="{fh:get(key: 'validations')}"/>
<f:variable name="errors" value="{fh:get(key: 'errors')}"/>
<f:variable name="formValuesPrefix" value="{fh:get(key: 'formValuesPrefix')}"/>
<f:variable name="values" value="{fh:get(key: 'values')}"/>
```
