# JSONSchema to PHP class converter

Build PHP classes from [JSON schemas][jsonschema] automatically.

## Example

Consider a simple JSON schema (ironically stored in YAML format), stored in a file
`example.yaml`:

```
required:
  - givenName
  - familyName
properties:
  givenName:
    type: string
  familyName:
    type: string
  hobbies:
    type: array
    items:
      type: string
  location:
    properties:
      country:
        type: string
      city:
        type: string 
```

Using this converter, you can automatically generate PHP classes from this schema
with accessor and conversion functions:

    $ vendor/bin/s2c generate:fromschema --class User ./example.yaml src/Target

This command will automatically try to infer a PHP target namespace from your `composer.json` file and automatically create the appropriate PHP classes:

    $ find src/Target
    src/Target
    src/Target/User.php
    src/Target/UserLocation.php

Then, use the classes in your code:

    $userData = json_decode("user.json", true);
    $user = \MyNamespace\Target\User::buildFromInput($userData);

    echo "Hello, " . $user->getGivenName() . "\n";

## Installation

Install using Composer:

    $ composer require --dev helmich/json-struct-builder

## Using configuration files

In many projects, you're going to want to keep an evolving JSON schema in sync with
the generated PHP classes continuously. For this reason, S2C allows
you to create a configuration file `.jsb.yaml` that stores the most common conversion
options:

```
targetPHPVersion: 5
files:
- input: src/Spec/Spec.yaml
  className: Specification
  targetDirectory: src/Spec
```

[jsonschema]: http://json-schema.org/