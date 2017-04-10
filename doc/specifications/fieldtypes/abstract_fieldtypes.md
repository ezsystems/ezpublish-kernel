# Abstract FieldTypes

Most FieldTypes implement the FieldType API directly. There is one exception, though: BinaryBase, used by
`BinaryFile` and `Media` (but not `Image`). Abstract FieldTypes are another application of this approach,
but this time aiming at simplifying FieldType for 3rd party implementors. This would be achieved by
providing re-usable FieldTypes that can easily be used for implementation.

## Audience
This implementation method is aimed at developers with little to no FieldType experience.
It hides most of the internal logic, and requires little to no knowledge of the FieldTypes and Persistence
internals.

## Use-cases
- Close derivatives of existing FieldType
  Example: TextLine with custom validation
- Standardized external storage 
  Example: a FieldType that interacts with a (set of) doctrine entity by storing a reference in the field.
- HTTP API consumer
  Example: the Tweet FieldType (a bit more far stretched, but worth digging)

## Approach

### Extended TextLine FieldType
A TextLine FieldType that can be extended, and can be assigned custom validators.
Configuration uses Constraints from the Symfony validator, as well as the same configuration mechanism.
Re-uses TextLine\Value, as it does not need to store anything else.

Unlike the default TextLine, it doesn't have a Length validation by default.

#### Usage
```yaml
ezplatform:
    scalar_types:
        french_zipcode:
            # Options: text, integer, float
            type: text
            constraints:
                - Regex:
                    pattern: '^[0-9]{5}$'
                    message: "A zip code must consist of 5 digits"
```

Would re-use the Validator component to validate and process the configuration.

#### Alternatives
One could also imagine more advanced configuration of the TextLine FieldType when editing a ContentType.
However, for complex configuration schemes, this might be very limited. Furthermore, this approach allows
for redistribution of valuable business cases.
