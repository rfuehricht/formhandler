# Formhandler - Validation



Formhandler uses `Validator` components to handle form validation.
By default, only a `DefaultValidator` is available which runs one or more `ErrorChecks` on the configured form fields.
You can easily add you own `Validator` or add a custom `ErrorCheck`.

<!-- TOC -->
* [DefaultValidator](#defaultvalidator)
  * [Available error checks](#available-error-checks)
  * [Custom error check](#custom-error-check)
* [Custom validator](#custom-validator)
<!-- TOC -->


## DefaultValidator

Normally, all components in Formhandler have to be explicitly set via `class = [ClassName]`.
For the `DefaultValidator`, this can be omitted or set to `default`.

These settings are all valid for using `DefaultValidator`.

```text
plugin.tx_formhandler.forms.myForm {
    settings {

        validators {
            1 {
                class = DefaultValidator
                config {

                }
            }
            1 {
                class = default
                config {

                }
            }
            1 {
                config {

                }
            }
        }

    }
}
```

The `DefaultValidator` has a few general settings. The most important is `fieldConf` with `errorCheck` for each field.

Example of a config:

```text
validators.1.config.fieldConf {

  // Basic checks
  name.errorCheck.1 = required
  email.errorCheck {
    1 = required
    2 = email
  }

  // File checks
  file.errorCheck {
    10 = fileRequired
    15 = fileAllowedTypes
    15.allowedTypes = jpg,gif,png
    20 = fileMaxSize
    20.maxSize = 5MB
    30 = fileMaxCount
    30.maxCount = 3
  }

  // Nested checks for array fields
  birthdate {
      day.errorCheck {
        1 = betweenValue
        1.minValue = 1
        1.maxValue = 31
      }
      month.errorCheck {
        1 = betweenValue
        1.minValue = 1
        1.maxValue = 12
      }
      year.errorCheck {
        1 = minValue
        1.minValue = 45
      }
  }
  birthdate.errorCheck.1 = maxItems
  birthdate.errorCheck.1.value = 3
}
```


| Setting                 | Type                 | Description                                                                                                                                                                                                                                      |
|-------------------------|----------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| disableErrorCheckFields | Comma separated list | List of field names to exclude form validation. Special keyword `all` may be used to completely disable validation.                                                                                                                              |
| restrictErrorChecks     | Comma separated list | List of field names to limit validation to. All other fields are NOT validated.                                                                                                                                                                  |
| messageLimit            | int                  | Define a global limit for error messages to be stored for fields. Use this if you only want the first error occurred to produce an error message in your form.                                                                                   |
| fieldConf               | array                | List of field names with error checks and custom message limit.<br><br>`messageLimit` - override global message limit for this field.<br>`errorCheck` - List of checks for this field.<br><br>See example above for details about the structure. |

### Available error checks

| Name             | Description                                                                       | Options                                                                                         |
|------------------|-----------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------|
| required         | Checks if field was filled out                                                    | none                                                                                            |
| email            | Checks if field value is a valid email address.                                   | none                                                                                            |
| maxLength        | Checks if field value is shorter or equal certain length.                         | `value` - The max length to check (int)                                                         |
| equalsField      | Checks if field value equal to the value of another field.                        | `field` - The name of the field to compare to                                                   |
| pregMatch        | Checks if field value equals given regular expression.                            | `value` - The regular expression to match                                                       |
| fileRequired     | Checks if a file was uploaded.                                                    | none                                                                                            |
| fileAllowedTypes | Checks if uploaded files match certain types.                                     | `allowedTypes`- The valid types (= file extensions) as comma separated list. E.g. `jpg,gif,png` |
| fileMaxCount     | Allows only a certain amount of files to be uploaded in a field.                  | `maxCount` - Maximum number of files allowed.                                                   |
| fileMaxSize      | Uploaded file must be smaller than a certain size.                                | `maxSize` - Size to check (Use human readable sizes like `5MB`)                                 |
| fileMaxTotalSize | Uploaded files in total must be smaller than a certain size                       | `maxSize` - Size to check (Use human readable sizes like `5MB`)                                 |
| fileMinCount     | Allows form submission only if a certain amount of files was uploaded in a field. | `minCount` - Minimum number of files allowed.                                                   |
| fileMinSize      | Uploaded file must have at least a certain size                                   | `minSize` - Size to check (Use human readable sizes like `5MB`)                                 |




### Custom error check

You can extend the available error checks with your custom checks.

```php
<?php

namespace Vendor\Theme\Extensions\Formhandler\ErrorCheck;

use Rfuehricht\Formhandler\Validator\ErrorCheck\AbstractErrorCheck;

class MyErrorCheck extends AbstractErrorCheck
{

    /**
     * Sets the suitable string for the checkFailed message parsed in view.
     *
     * @param string $fieldName
     * @param array $values
     * @param array $settings
     * @return string|array If the check failed, the string contains the name of the failed check plus the parameters and values.
     */
    public function check(string $fieldName, array $values, array $settings = []): string
    {
        //Do validation

        //Return key of error check that failed on failed validation. Used by Formhandler to determine error message key in translation file.
        return 'myCheck';

        //Return empty string if validation was successful.
        return '';
    }

}
```

Afterward, configure it in TypoScript:

```text

plugin.tx_formhandler.forms.myForm {
    settings {

        validators {
            1.class = default
            1.config.fieldConf {
                fieldName.errorCheck.1 = Vendor\Theme\Extensions\Formhandler\ErrorCheck\MyErrorCheck
            }
        }

    }
}

```

## Custom validator

Add you own class by extending `AbstractValidator`.

```php
<?php

namespace Vendor\Theme\Extensions\Formhandler\Validator;

use Rfuehricht\Formhandler\Validator\DefaultValidator;

class MyCustomValidator extends DefaultValidator
{

    /**
     * Validates the submitted values using given settings
     *
     * @param array &$errors Reference to the errors array to store the errors occurred
     * @return boolean
     */
    public function validate(array &$errors): bool
    {
        /* Fill $errors if your validation fails.
           Formhandler uses this format to handle errors:

            $errors = [
                'nameOfTheFormField' => [
                    'nameOfTheError',
                    'nameOfAnotherError'
                ]
            ];
        */

        return empty($errors);
    }
}
```

Add this class to TypoScript:

```text

plugin.tx_formhandler.forms.myForm {
    settings {

        validators {
            20.class = Vendor\Theme\Extensions\Formhandler\Validator\MyCustomValidator

        }

    }
}
```
