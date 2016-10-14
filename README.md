# PHPGoodies - Validator

Simple & reusable input/data validator.

## Usage

```
$result = Validator::validate([{data}])
    ->verify('{field}', '{rule(s)}', [{custom-messages}])
    ->verify...
    ->run();
```

### Defining rules

Available rules: required, min, & max.

Multiple rules are separated by a |.

Additional arguments are separated by a :.

Example: "required|min:5|max:10"

### Result


