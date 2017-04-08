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
