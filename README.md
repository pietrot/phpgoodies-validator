# PHPGoodies - Validator

Simple & reusable input/data validator.

## Usage

```
$result = Validator::validate({data:array})
    ->verify('{field:string}', '{rules:string}', {custom-messages:array})
    ->verify...
    ->run();

```

### Defining rules

- Available rules: required, min, max, alpha, alpha_num, int, and decimal.
- Multiple rules are separated by a "|".
- Additional arguments (such as min/max value, etc.) are separated by a ":".
  Example: "required|min:5|max:10"

### Custom messages

Adding custom messages is a breeze. Simply provide an associative array with keys being the rule,
and values being the messages when errors are caught.

Example:

```
$result = Validator::validate([])
    ->verify('name', 'required', ['required' => "Don't you be missin' no name!"])
    ->run();

```

### Result

The result returned from running the validator is of type ValidatorResult. A helper function is
provided to convert it to a friendly array.

```
$result->toArray();
```


*That's all folks :)*
