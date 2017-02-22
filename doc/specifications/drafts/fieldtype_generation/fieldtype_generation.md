# FieldType generation proof-of-concept

The file [`fieldtypes.yml`](fieldtypes.yml) describes the properties of a FieldType,
the TextLine one, using yaml.

It articulates around the definition of properties, for both the FieldType's Value
and the FieldType's Definition (as in FieldDefinition, the "instance" of a FieldType
in a ContentType).

## Properties mapping

Each property will be mapped to a Value property:

The following configuration block:
```yaml
ezstring:
    value_properties:
        text:
            type: string
```

Matches the following PHP Class:

```php
namespace eZ\Publish\Core\FieldType\TextLine;

class Value implements ValueObject
{
    /**
     * @var string
     */
    public $text;
}
```

Properties can be defined in the same way for the field definition. However, due to how
FieldTypes are architectured, it won't generate an object. Instead, it will generate
code in the `validateValidatorConfiguration()` FieldType method.

## Constraints mapping
For each property, an optional constraints key can be defined:

```yaml
ezstring:
    definition_properties:
        minLength:
            type: integer
            constraints:
                - Range: [0-255]
                - LesserThanOrEqual: self.maxLength
```

The `minLength` FieldDefinition property has to be within the 0-255 range, and can't be
higher than the maxLength property.

The `self.<propertyName>` syntax is used to reference _other constraints_.

A similar syntax can be used in value properties definitions:

```yaml
ezstring:
    value_properties:
        text:
            type: string
            constraints:
                - Length:
                    min: definition.minLength
                    max: definition.maxLength
```

`definition.<propertyName>` is used to refer to a properties from the FieldDefinition.
Above, the `minLength` and `maxLength` properties are used as the minimum and maximum length
for the TextLine value.

## Storage mapping
For simple cases where no external storage is required, properties can be mapped to
the legacy storage field where they are stored:

```yaml
ezstring:
    definition_properties:
        minLength:
            type: integer
            legacy_storage: data_int_1
        maxLength:
            type: integer
            legacy_storage: data_int_2
        defaultValue:
            type: string
            legacy_storage: data_text_1
    value_properties:
        text:
            type: string
            legacy_storage: data_text
```

### Storage auto-mapping ?
We could, by default, if no `legacy_storage` is specified, map sequentially based
on the type & order ?

- `minLength` is the 1st definition integer => data_int_1
- `maxLength` is the 2nd definition integer => data_int_2
- `defaultValue` is the 1st definition string => data_text_1
- `text` is a string => data_text

### External storage
If external storage gets involved, it will be harder to auto-generate.
