# Formhandler - Templating

Formhandler template files are mostly "normal" FLUID templates and basically use the FLUID form view helpers provided by TYPO3 core.
To make things easier, Formhandler provides some information as FLUID variables and offers [ViewHelpers](./ViewHelpers.md).

Each step of a form has its own template file.

Example:

```html
<html
    data-namespace-typo3-fluid="true"
    lang="en"
    xmlns:f="http://typo3.org/ns/fluid/ViewHelpers"
    xmlns:fh="http://typo3.org/ns/Rfuehricht/Formhandler/ViewHelpers">

<f:form action="form">
    <f:form.hidden name="{formValuesPrefix}[randomId]" value="{randomId}" />

    <f:if condition="!{isRequired}">
        <f:for each="{validations.name}" as="check">
            <f:if condition="{check.check} == 'required'">
                <f:variable name="isRequired" value=" *" />
            </f:if>
        </f:for>
    </f:if>
    <label for="name">{fh:translate(key:'name')}{isRequired}</label>
    <f:form.textfield id="name"
                      name="{formValuesPrefix}[name]"
                      placeholder="{fh:translate(key:'name')}"
                      value="{values.name}"
    />
    <f:if condition="{errors.name}">
        <ul>
            <f:for as="error" each="{errors.name}">
                <li>{error}</li>
            </f:for>
        </ul>
    </f:if>

    <f:form.submit name="{formValuesPrefix}[{submit.nextStep}]" value="{fh:translate(key:'submit')}" />
</f:form>
```

This form will show an input field "name" and a submit button. The labels are loaded from a translation file configured in TypoScript.
By looping through all configured `validations` and finding out if a field is configured to be required, a `*` is added to the field label.

## FLUID variables

These variables are available in the template.

| Name             | Type   | Description                                                                                                                                                                                                                                                            |
|------------------|--------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| formValuesPrefix | string | The `formValuesPrefix` set in TypoScript.                                                                                                                                                                                                                              |
| values           | array  | The submitted form values.                                                                                                                                                                                                                                             |
| settings         | array  | The TypoScript configuration (for the current step).                                                                                                                                                                                                                   |
| errors           | array  | The errors occurred (for the current step).                                                                                                                                                                                                                            |
| validations      | array  | The validation rules (for the current step).                                                                                                                                                                                                                           |
| currentStep      | int    | The current step index starting with `1`                                                                                                                                                                                                                               |
| randomId         | string | A unique hash for each form instance, preventing problems with multiple forms on a page. The `randomId` **MUST** be passed in any case as hidden field.                                                                                                                |
| files            | array  | Information about uploaded files as array.                                                                                                                                                                                                                             |
| submit           | array  | Information about submit button names.<br><br>`previousStep` - Name to use for submit button to previous step.<br>`nextStep` - Name to use for submit button to next step.<br>`reload` - Name to use for a button reloading the current step. Useful for file uploads. |

### Show errors for a field

Loop available data in `errors` to show error messages for a field.

```html
<f:if condition="{errors.name}">
    <ul>
        <f:for as="error" each="{errors.name}">
            <li>{error}</li>
        </f:for>
    </ul>
</f:if>
```

`error` contains the translated message for the error occurred, if available.

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

### File upload fields


```html
<f:form.upload additionalAttributes="{multiple: 'multiple'}" name="{formValuesPrefix}[myFileField]" value="{values.myFileField}"/>
<f:form.submit name="{formValuesPrefix}[{submit.reload}]" value="{fh:translate(key: 'upload')}" />
<f:if condition="{errors.myFileField}">
    <ul>
        <f:for as="error" each="{errors.myFileField}">
            <li>{error}</li>
        </f:for>
    </ul>
</f:if>
<f:if condition="{files.myFileField}">
    <ul>
        <f:for as="file" each="{files.myFileField}" key="index">
            <li>{file.name} ({file.size -> f:format.bytes()})
                <fh:fileRemoveButton file="{file}">X</fh:fileRemoveButton>
        </f:for>
    </ul>
</f:if>
```

This example code renders a file upload field allowing upload of multiple files.

Next to the field is a submit button which triggers a reload of the form only doing validation and upload of file upload fields and showing current step again.

The FLUID variable `files` holds information about the uploaded files. It can be looped to show the file name and size and render a removal button using a [ViewHelper](./ViewHelpers.md).
