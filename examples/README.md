# Examples

This folder contains example schema files and their generated output.

## `basic`

Very straighforward without any advanced structures: One main class `User` with several properties,
two of them required, and a subclass `UserLocation` with optional properties.

In order to create the output in the `generated` subfolder, the following command was used:

`cmd/s2c generate:fromschema --class User --target-namespace Example\Basic ./examples/basic/basic-example.yaml examples/basic/generated`

## `advanced`

The advanced example contains more constraints for individual properties, like enums, string length
etc, as well as alternative structures due to `oneOf` and arrays containing objects.

`cmd/s2c generate:fromschema --class User --target-namespace Example\Advanced ./examples/advanced/advanced-schema.yaml examples/advanced/generated`
