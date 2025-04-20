# Formhandler - ViewHelpers

Formhandler provides some view helpers to make templating easier.

<!-- TOC -->
* [form](#form)
* [translate](#translate)
* [errorMessages](#errorMessages)
* [fileRemoveButton](#fileremovebutton)
* [get](#get)
  * [Available keys](#available-keys)
<!-- TOC -->

## form

Formhandler requires a hidden field `randomId` to be set in every form.

The `action` of the form should always be set to `form` to lead to the `formAction` in Formhandler.

If you are having file upload fields in your form, make sure to set `enctype=multipart/form-data` to your form.

For convenience, Formhandler offers a `form` view helper taking care of these setting.
It extends the `form` view helper of TYPO3 and adds the additional information Formhandler needs.


```html
<html
    data-namespace-typo3-fluid="true"
    xmlns:f="http://typo3.org/ns/fluid/ViewHelpers"
    xmlns:fh="http://typo3.org/ns/Rfuehricht/Formhandler/ViewHelpers">

<fh:form>

    <!-- ... -->

</fh:form>

</html>
```

## errorMessages

Get error messages for a field to render them in a FLUID template.

Arguments:

| Argument | Type   | Description                                                                    |
|----------|--------|--------------------------------------------------------------------------------|
| field    | string | Field to get the error messages for.                                           |
| as       | string | Optional name of the variable to store the messages in. Default: `errors`      |
| error    | string | Optional name of a specific error to get the messages for. Default: all errors |

Example:

```html
<html
    data-namespace-typo3-fluid="true"
    xmlns:fh="http://typo3.org/ns/Rfuehricht/Formhandler/ViewHelpers">

<fh:errorMessages field="name">
    <ul>
        <f:for each="{errors}" as="message" >
            <li>{message}</li>
        </f:for>
    </ul>
</fh:errorMessages>

</html>
```

`message` contains the translated message for the error occurred, if available.

The message is searched in key `error_[fieldName]_[errorKey]`. Fallback is `error_default_[errorKey]`.
If no message is found, the error key is returned, e.g. `required`.

Example:

```xml
<trans-unit id="error_default_required">
    <source>{fieldName} is required</source>
</trans-unit>
<trans-unit id="error_file_fileMaxSize">
    <source>{fieldName} max size is {maxSize}</source>
</trans-unit>
```

You can use FLUID style variable syntax in the error messages. `fieldName` is replaced with the name of the form field triggering the error, all other variables are the names of the options of the error check.
Additionally, you can use `{LLL:[key]}` to get translated content from inside the translation message.

Example:

```xml
<trans-unit id="error_default_required">
    <source>{LLL:label_{fieldName}} is required!</source>
</trans-unit>
```

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
