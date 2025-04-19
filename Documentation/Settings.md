# Formhandler - Global settings

The plugin namespace to use for all TypoScript configuration is `plugin.tx_formhandler`.

Use the settings `view` and `settings` to define configuration for **all** forms.

All your forms should be configured inside `forms` to be available in the dropdown "Predefined forms" in the plugin.

Each form has three top level configurations: `name`, `view`, `settings`.

| Setting  | Description                                                                                 |
|----------|---------------------------------------------------------------------------------------------|
| name     | Name of the form in the dropdown in plugin settings. Default: The key of the configuration. |
| view     | `templateRootPaths` (and `partialRootPaths` and `layoutRootPaths`) of this form.            |
| settings | All other settings for this form. Detail described below.                                   |


Example:

```text
plugin.tx_formhandler {
  # Settings for all forms
  view.partialRootPaths.10 = EXT:theme/Resources/Private/Forms/Partials/

  # Specific forms
  forms {
    contact {
      name = Contact form
      view.templateRootPaths.10 = EXT:theme/Resources/Private/Forms/Contact/
      settings {

      }
    }
    checkout {
      name = Checkout form
      view.templateRootPaths.10 = EXT:theme/Resources/Private/Forms/Checkout/
      settings {

      }
    }
  }
}
```

The available options in `settings` are:

| Setting          | Type          | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
|------------------|---------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| templateFile     | string        | A template file (without path and extension) to be used for this form. If the form has multiple steps, each step must have a template file.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| formValuesPrefix | string        | A custom string to prefix all form fields with. Recommended to be used to prevent conflicts with other components.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |
| languageFile     | string, array | Specify one or more translation files to be used within view helper `translate` (See section [ViewHelpers](ViewHelpers.md)).                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| preProcessors    | array         | Array of [Components](Components.md) to be run as pre-processors. Pre-processors are run only once, when the form is initially loaded and shows the first step.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
| interceptors     | array         | Array of [Components](Components.md) to be run as interceptors. Interceptors are run everytime, when the form is loaded (After validation, after loading a new step, ...)                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| finishers        | array         | Array of [Components](Components.md) to be run as finishers. Finishers are run after a form is finished successfully, which means validation is successful and form has no more steps to show.                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| validators       | array         | Array of validations to be run. See section [Validation](Validation.md) for more details.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| skipView         | bool (0,1)    | If set to `1`, Formhandler doesn't show an HTML template, it immediately runs pre-processors, interceptors and finishers.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| useForm          | string        | Specify key of a predefined form to use. This renders a certain form without taking care of plugin settings in flex forms.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
| files            | array         | Settings for file uploads.<br><br>`uploadedFilesWithSameName` - Configure what to do if a file exists in the upload folder.<br><br>`ignore` - Default. Use existing file and ignore uploaded file.<br>`replace` - Replace existing file with new file.<br>`append` - Append numeric suffix for the new file. Both files are stored.<br><br>`uploadFolder`, `uploadFolder.[fieldname]`- Specify upload folder to use.<br>`search` - comma separated list of characters to replace in file names.<br>`replace` - Comma separated list of replacements for `search`.<br>`usePregReplace` - If set to `1`, `search` is treated as regular expression. |


## Multi step forms

Additionally, for forms with multiple steps, all settings can be set for a specific step. Use it to set template files, interceptors and validation rules for each step.

```text
plugin.tx_formhandler {
    forms.contact {
        settings {
            // Run for every step
            interceptors {

            }
            1 {
                templateFile = Step1
                validators {

                }
            }
            2 {
                templateFile = Step2

                // Run for second step only.
                interceptors {

                }

                validators {

                }
            }
        }
    }
}
```

## Conditional settings

Formhandler offers a custom condition to access form values inside TypoScript.
Use conditions to adjust your forms according to data input.


Example:

Use a different template file and validations if a certain value was set.

```text
[traverse(formhandlerValues('contact'), 'name') == 'hugo234']
  plugin.tx_formhandler.forms.contact.settings {
    2 {
        templateFile = Step2VariantB
        validators {

        }
    }
  }
[end]

```

`formhandlerValues('contact')` The string `contact` here is the `formValuesPrefix` of the form, **NOT** the form key.
