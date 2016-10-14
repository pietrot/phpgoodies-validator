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

### Result

The result returned from running the validator is of type ValidatorResult. A helper function is
provided to convert it to a friendly array.

```
$result->toArray();
```


*That's all folks :)*
