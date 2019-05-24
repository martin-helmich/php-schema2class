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

## Creation result

The generated classes have these features:

- Namespace either taken from your composer.json or defined by the command line.
- The main object's name is defined by the commandline.
- Subobjects's names are taken from the property name.
- Array items are suffixed 'Item'.
- `OneOf` alternatives are suffixed 'AlternativeX', with `X` being an incremented integer.
- The constructor has arguments for all required properties in the schema.
- All properties are private, with getter methods for access, and explicit type declarations for the return value 
(in PHP5 mode, only PHPDoc is used).
- Static function `buildFromInput(array $data)` accepts an array (using `json_decode('{}', true)`), validates it 
according to the schema and creates the full object tree as return value. An additional mapping step is not required.
- Function `toJson()` returns a plain array ready for `json_encode()`.
- Writing to any object's properties is done immutably by using `withX()` (or `withoutX()` for optional values). This will return
a new instance of that object with the value changed.

As an example, a shortened version with all comments removed, from the above schema shows the location, only containing the city (country is behaving the same, but with a different name)

    class UserLocation
    {
        private static $schema = array(
            'properties' => array(
                'city' => array(
                    'type' => 'string',
                ),
            ),
        );
    
        private $country = null;
    
        private $city = null;
    
        public function __construct()
        {
        }
    
        public function getCity() : ?string
        {
            return $this->city;
        }
    
        public function withCity(string $city) : self
        {
            $validator = new \JsonSchema\Validator();
            $validator->validate($city, static::$schema['properties']['city']);
            if (!$validator->isValid()) {
                throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
            }
    
            $clone = clone $this;
            $clone->city = $city;
    
            return $clone;
        }
    
        public function withoutCity() : self
        {
            $clone = clone $this;
            unset($clone->city);
    
            return $clone;
        }
    
        public static function buildFromInput(array $input) : UserLocation
        {
            static::validateInput($input);
    
            $city = null;
            if (isset($input['city'])) {
                $city = $input['city'];
            }
    
            $obj = new static();
            $obj->city = $city;
            return $obj;
        }
    
        public function toJson() : array
        {
            $output = [];
            if (isset($this->city)) {
                $output['city'] = $this->city;
            }
    
            return $output;
        }
    
        public static function validateInput(array $input, bool $return = false) : bool
        {
            $validator = new \JsonSchema\Validator();
            $validator->validate($input, static::$schema);
    
            if (!$validator->isValid() && !$return) {
                $errors = array_map(function($e) {
                    return $e["property"] . ": " . $e["message"];
                }, $validator->getErrors());
                throw new \InvalidArgumentException(join(", ", $errors));
            }
    
            return $validator->isValid();
        }
    
        public function __clone()
        {
        }
    }

## Installation

Install using Composer:

    $ composer require --dev helmich/schema2class

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
