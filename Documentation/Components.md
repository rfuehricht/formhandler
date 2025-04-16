# Formhandler Components

Components are classes that can be added as pre processors, interceptors or finishers of a form.

## loadGetPost

Loads current GET/POST parameters to the internal values. This makes the most sense as a pre processor, when an external script is passing data to the form. By default Formhandler ignores values that are passed initially.

Example:

```
preProcessors {
    10.class = loadGetPost
}
```

## loadDefaultValues

Prefills form fields with values. This makes the most sense as a pre processor.

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
