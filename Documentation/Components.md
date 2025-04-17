# Formhandler Components

Components are classes that can be added as pre-processors, interceptors or finishers of a form.

<!-- TOC -->
* [loadGetPost](#loadgetpost)
* [loadDefaultValues](#loaddefaultvalues)
* [redirect](#redirect)
* [renderTemplate](#rendertemplate)
* [dbSave](#dbsave)
  * [Special Mappings](#special-mappings)
* [email](#email)
* [generateAuthCode](#generateauthcode)
* [validateAuthCode](#validateauthcode)
<!-- TOC -->

## loadGetPost

Loads current GET/POST parameters to the internal values. This makes the most sense as a pre-processor, when an external script is passing data to the form. By default, Formhandler ignores values that are passed initially.

Example:

```
preProcessors {
    10.class = loadGetPost
}
```

## loadDefaultValues

Prefills form fields with values. This makes the most sense as a pre-processor.

Example:

```
preProcessors {
    10.class = loadDefaultValues
    10.config {
        name = My Value
        email = TEXT
        email.value = example@domain.tld
    }
}
```

## redirect

Redirect to another page. This makes the most sense as a finisher.

Options:

| Setting           | Type  | Description                                     |
|-------------------|-------|-------------------------------------------------|
| redirectPage      | int   | Page ID to redirect to.                         |
| statusCode        | int   | HTTP Status Code of the redirect. Default: 302  |
| additionalHeaders | array | Additional headers to send with the redirect.   |


Example:

```
finishers {
  10.class = redirect
  10.config {
    redirectPage = 12
  }
}
```

## renderTemplate

Render a FLUID template after successful form submission. This makes the most sense as a finisher.

Options:

| Setting           | Type   | Description                                    |
|-------------------|--------|------------------------------------------------|
| templateFile      | string | Template name (without path)                   |


FLUID variables:

| Variable | Type  | Description                    |
|----------|-------|--------------------------------|
| config   | array | The settings of this component |
| values   | array | The submitted form values      |

Example:

```
finishers {
  10.class = renderTemplate
  10.config {
    templateFile = SubmittedOk
  }
}
```

## dbSave

Store form values in a database table.
Information about the stored data is saved and passed on to all following components.

Options:

| Setting                         | Type       | Description                                                                                                                                                                                                        |
|---------------------------------|------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| table                           | string     | The table to store data in                                                                                                                                                                                         |
| key                             | string     | The key field. Used to determine if record exists, when using **updateInsteadOfInsert**. Also passed to following components as info. Default: uid                                                                 |
| keyValue                        | string     | The value of the key field used to determine if record exists, when using **updateInsteadOfInsert**. If not set, the value of a form field with the name of setting **key** is used. E.g. tx_formhandler_form[uid] |
| updateInsteadOfInsert           | bool (0,1) | Search for an existing record using **key** and **keyValue** setting. If found, the existing record is updated.                                                                                                    |
| fields                          | array      | Form values to database field mapping. Key is the database field, value is the configuration.                                                                                                                      |
| fields.[dbField].mapping        | string     | Mapping contains the name of a form field. The value of the field is saved into the given database column.                                                                                                         |
| fields.[dbField].ifIsEmpty      | string     | If the mapped form value is empty, save the value configured here.                                                                                                                                                 |
| fields.[dbField].zeroIfEmpty    | bool (0,1) | If the mapped form value is empty, save a NULL value to database column.                                                                                                                                           |
| fields.[dbField].special        | string     | Special mapping for the database column. Available options are: `ip`, `sub_tstamp`, `sub_datetime`, `datetime`, `inserted_uid`, `files`. Details below in section [Special mappings](#special-mappings)            |


### Special Mappings


| Name         | Description                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
|--------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| ip           | Stores the IP address of the user. Uses `$_SERVER['REMOTE_ADDR']` by default. If set, `$_SERVER['HTTP_X_REAL_IP']`, is used. You can override this by settings `customProperty`, e.g. `special.customProperty = MY_KEY_IN_SERVER_ARRAY`.                                                                                                                                                                                                                                                                                                                                                                                                                              |
| sub_tstamp   | Stores the timestamp of form submission.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              |
| sub_datetime | Stores the date and time of form submission. Use `format` to specify your custom format. Default is `Y-m-d H:i:s`.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| datetime     | Stores the date and time of form a form field. Use `format` to specify your custom format. Default is `Y-m-d H:i:s`. Use `field` to specify the form field.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| inserted_uid | Stores the UID value of a record inserted by `dbSave` before. Use setting `table` to specify which inserted record to use. Useful for storing 1:n relations.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
| files        | Stores file names or infos of uploaded files. <br><br>`field` is the field name of the upload field.<br>`separator` is an optional separator for file arrays (Default: `,`)<br>`index` is an optional index to access in the files array. Use this if you want to access information of a specific file (e.g. the second one uploaded).<br>`info` is a scheme which info should be stored.<br><br>Available info:<br>uploaded_path - Absolute upload path<br>uploaded_name - Name of the file<br>uploaded_folder - Relative upload path<br>uploaded_url - URL to the uploaded file<br>name - Original file name<br>size - File size in byte<br>type - The MIME type   |


Example:

```
finishers {
  10.class = dbSave
  10.config {
    table = tx_theme_submission
    fields {
      name.mapping = name
      email.mapping = email
      crdate.special = sub_tstamp
      hidden = 1
    }
  }
}
```

## email

Send an email.

Options:

| Setting      | Type         | Description                                                                                                                                                                              |
|--------------|--------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| templateFile | string       | The template file (without path and file extension) of the mail. Formhandler uses `FluidEmail` to send the mails, so Partials and Layouts should be available from global mail settings. |
| subject      | string       | Subject of the email.                                                                                                                                                                    |
| sender       | address      | Consists of `email` and an optional `name` setting.                                                                                                                                      |
| to           | array        | List of recipients. Each entry consists of `email` and an optional `name` setting.                                                                                                       |
| cc           | array        | List of recipients. Each entry consists of `email` and an optional `name` setting.                                                                                                       |
| bcc          | array        | List of recipients. Each entry consists of `email` and an optional `name` setting.                                                                                                       |
| replyTo      | array        | List of recipients. Each entry consists of `email` and an optional `name` setting.                                                                                                       |
| returnPath   | address      | Consists of `email` and an optional `name` setting.                                                                                                                                      |
| attachments  | string,array | List or array of form upload fields or static file names to attach.                                                                                                                      |


FLUID variables:

| Variable | Type  | Description                    |
|----------|-------|--------------------------------|
| config   | array | The settings of this component |
| values   | array | The submitted form values      |

Example:

```text
finishers {
  10.class = email
  10.config {
    templateFile = ContactMailUser
    to {
      1 {
        email = email
        name = name
      }

      2 {
        email = example@domain.tld
      }
    }

    attachments = file
  }
}
```


## generateAuthCode

Generate a unique hash for a database row inserted by [dbSave](#dbsave).

| Setting       | Type                 | Description                                                                                                                                                                                                                                |
|---------------|----------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| table         | string               | The table to take the data from.                                                                                                                                                                                                           |
| uidField      | string               | Field to use as key field to search the data. Default: `uid`                                                                                                                                                                               |
| uid           | string               | UID value to use.                                                                                                                                                                                                                          |
| selectFields  | string               | List of fields to select. Default: `*`                                                                                                                                                                                                     |
| authCodePage  | Page ID              | Page ID of where the hash validation takes place. Default: Current page                                                                                                                                                                    |
| excludeParams | Comma separated list | List of parameters to exlude from generated URL to auth page. By default, `authCode`, `table`, `uidField` and `uid` are set. If you specify table, uidField and uid in the configuration of `validateAuthCode`, you can exclude them here. |

If you want to use the data of a single record just inserted with `dbSave`, no further configuration is needed. Formhandler searches and uses this data automatically.

Basic example:

```text
finishers {
  10.class = generateAuthCode
  10.config {

  }
}
```

Complex example:

```text
finishers {
  10.class = generateAuthCode
  10.config {
    table = my_custom_table
    uidField = id
    uid = 1234
    selectFields = field1,field2,field3
  }
}
```

## validateAuthCode

Validate a hash generated by [generateAuthCode](#generateAuthCode).
This component should be used as a `preProcessor` in combination with `loadGetPost`.

If the validation is successful, the record is updated as specified.
By default, a record having the `hiddenField` set to `hiddenStatusValue` is selected and updated with `activeStatusValue`.
You can specify redirect pages or content to show after successful or failed validation.

| Setting           | Type   | Description                                                                                                                                                                                                                                                                      |
|-------------------|--------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| table             | string | The table to take the data from. Default is the GET parameter `table`.                                                                                                                                                                                                           |
| uidField          | string | Field to use as key field to search the data. Default is the GET parameter `uidField`.                                                                                                                                                                                           |
| uid               | string | UID value to use. Default is the GET parameter `uid`.                                                                                                                                                                                                                            |
| selectFields      | string | List of fields to select for hash generation. Should be the same as configured in `generateAuthCode`. Default: `*`                                                                                                                                                               |
| hiddenField       | string | Name of the field indicating the hidden state. Default is the field set in TCA `['ctrl']['enablecolumns']['disabled']`                                                                                                                                                           |
| hiddenStatusValue | string | Value indicating the hidden state. Default: `1`                                                                                                                                                                                                                                  |
| success           | array  | In case of successful hash validation.<br><br>`redirectPage` - Page ID to redirect to.<br>`content` - Content to show (string or cObject).<br>`statusCode`- HTTP status code of the redirect. Default: 302<br>`additionalHeaders` - Additional headers for the redirect (Array). |
| error             | array  | In case of failed hash validation.<br><br>`redirectPage` - Page ID to redirect to.<br>`content` - Content to show (string or cObject).<br>`statusCode`- HTTP status code of the redirect. Default: 302<br>`additionalHeaders` - Additional headers for the redirect (Array).     |


Example:

```text
preProcessors {
  # Use loadGetPost to load the query parameters from URL
  10.class = loadGetPost
  20.class = validateAuthCode
  20.config {
    success.redirectPage = 20
    error.content = Hash invalid
  }

}

```
