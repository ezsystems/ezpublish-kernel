# RichText field type implementation

This document provides an overview of RichText field type implementation.

## Formats

### Internal format

The RichText field type defines it's internal format in XML, as a variation of [DocBook V5.0 format](http://docbook.org/ns/docbook). DocBook format requires version attribute on the root element, which is recommended to follow a specific format if the schema is customized. In our case, version attribute looks like this:

```
5.0-variant ezpublish-1.0
```

This is interpreted as: eZ Publish (eZ Platform) variation 1.0 of DocBook 5.0.
For the moment version attribute is not used, but in the future it could be used to handle migration when doing changes to the format.

#### Schema

The internal format RELAX NG schema can be found in:

```
eZ/Publish/Core/FieldType/RichText/Resources/schemas/docbook/ezpublish.rng
```

Shema uses unmodified DocBook RELAX NG (normative for DocBook) schema as a base for replacement and extension:

 - `docbook.rng`

Together with RELAX NG schema it is also recommended to validate documents using Schematron rules:

 - `docbook.sch`

As Schematron rules from Docbook 5.0 release are in old Schematron 1.5 format, which is not handled, this file is converted to new ISO Schematron:

 - `docbook.iso.sch`

ISO Schematron is handled through XSLT transformation, so XSLT stylesheet generated from ISO Schematron is also provided:

 - `docbook.iso.sch.xsl`

Conversion stylesheets together with command line script are also provided:

```
eZ/Publish/Core/FieldType/RichText/Resources/stylesheets/schematron
```

Aside from schema, the format is also described by test fixtures, on which in more detail below.

### XHTML5 formats

#### XHTML5 output format

XHTML5 output format is used for rendered output, displayed in the browser. The format's namespace is:

```
http://ez.no/namespaces/ezpublish5/xhtml5
```

XSD schema for this format can be found in:

```
eZ/Publish/Core/FieldType/RichText/Resources/schemas/ezxhtml5/output
```

Test fixtures describing the format are located in:

```
eZ/Publish/Core/FieldType/Tests/RichText/Converter/Xslt/_fixtures/xhtml5/output
```

#### XHTML5 edit format

XHTML5 edit format is used for HTML5 client side editor. The format's namespace is:

```
http://ez.no/namespaces/ezpublish5/xhtml5/edit
```

The format is described by test fixtures:

```
eZ/Publish/Core/FieldType/Tests/RichText/Converter/Xslt/_fixtures/xhtml5/edit
```

Schema for this format was removed in https://github.com/ezsystems/ezpublish-kernel/pull/1435.

### Legacy eZXML format

The field type provides conversion to and from legacy eZXML format, used by XmlText field type. For the purpose of testing the conversion result the field type contains the schema for the format:

```
eZ/Publish/Core/FieldType/RichText/Resources/schemas/ezxml
```

Test fixtures describing this format can be found in:

```
eZ/Publish/Core/FieldType/Tests/RichText/Converter/Xslt/_fixtures/ezxml
```

Note that this format does not have a default namespace, for that reason it is handled as a special case in validator and converter dispatchers (described below).

## Validation

Validation is performed by dispatching a `Validator` service configured with XSD, RELAX NG and/or Schematron schemas. Validators are dispatched by `ValidatorDispatcher` service, which is configured with `Validator` services, per document's namespace. Therefore the concrete `Validator` is dispatched by XML document's namespace, which is then also the way a specific format is recognized.

Note that for each supported format, a validator must be configured. If the format is not to be validated, the namespace should be configured with `null` value.

The implementation can be found here:

 - `eZ/Publish/Core/FieldType/RichText/Validator`
 - `eZ/Publish/Core/FieldType/RichText/ValidatorDispatcher`

## Conversion

Conversion is performed by dispatching a `Converter` implementation. Conversions are dispatched by `ConverterDispatcher` service, which is configured with `Converter` services, per document's namespace. Therefore the concrete `Converter` is dispatched by XML document's namespace, which is then also the way a specific format is recognized. Converter always receives PHP's `DOMDocument` instance for conversion, and returns PHP's `DOMDocument` instance as a conversion result.

Note that for each supported format, a converter must be configured. If the format is not to be converted (as is the case with internal format), the format's namespace should be configured with `null` value.

Main converter implementations are `Aggregate` and `Xslt`.

  - `eZ/Publish/Core/FieldType/RichText/Converter/Aggregate`

    `Aggregate` converter performs conversion by aggregation of other converters, applied in
    prioritized order.
  - `eZ/Publish/Core/FieldType/RichText/Converter/Xslt`

    `Xslt` converter performs conversion by configured XSLT stylesheet and optionally an array of
    custom prioritized stylesheets.

Conversion stylesheets for `Xslt` converter can be found in:

```
eZ/Publish/Core/FieldType/RichText/Resources/stylesheets
```

Some of the stylesheets found there are broken down into multiple components. This is done in order to enable customization together with `Xslt` converter, where the one stylesheet is provided as main and other are provided as custom stylesheets to the converter. Default values for these are configured in `eZ/Bundle/EzPublishCoreBundle/Resources/config/default_settings.yml` and are processed by the RichText field type configuration parser:

```
eZ/Bundle/EzPublishCoreBundle/DependencyInjection/Configuration/Parser/FieldType/RichText
```

Aside from `Aggregate` and `Xslt` converters, which are general implementations, there is a number of other specialized converters, which will be mentioned below. Important thing to know here is that, even in the case when special converters are also used, main conversion will always be performed by `Xslt` converter.

### Internal => XHTML5 output

Conversion from the internal format to the rendered XHTML5 output involves a number of separate converters, configured with the main `Aggregate` converter to act as one.

 1. `eZ/Publish/Core/FieldType/RichText/Converter/Link`

    `Link` converter converts internal links to proper browser URLs.
 2. `eZ/Publish/Core/FieldType/RichText/Converter/Render/Template`

    `Template` converter injects rendered template (custom tags in Legacy speak) tags into the document.
 3. `eZ/Publish/Core/FieldType/RichText/Converter/Render/Embed`

    `Embed` converter injects rendered embed tags into the document.
 4. `Xslt` converter - this is the *main* converter, configured with stylesheets found in:

   ```
   eZ/Publish/Core/FieldType/RichText/Resources/stylesheets/docbook/xhtml5/output
   ```
 5. `Xslt` converter - configured with stylesheet that produces embeddable fragment:

   ```
   eZ/Publish/Core/FieldType/RichText/Resources/stylesheets/xhtml5/output/fragment.xsl
   ```

Note that `Template` and `Embed` converters depend on `eZ\Publish\Core\FieldType\RichText\RenderedInterface` interface. Single implementation of this interface is located in the MVC domain:

```
eZ\Publish\Core\MVC\Symfony\FieldType\RichText\Renderer
```

### Internal => XHTML5 edit

Conversion from the internal format to the XHTML5 format for the client side editor is performed by a `Xslt` converter configured with stylesheets found in:

```
eZ/Publish/Core/FieldType/RichText/Resources/stylesheets/docbook/xhtml5/edit
```

### XHTML5 edit => Internal

Conversion from XHTML5 coming from the client side editor to the internal format is performed by a `Xslt` converter configured with a stylesheet:

```
eZ/Publish/Core/FieldType/RichText/Resources/stylesheets/xhtml5/edit/docbook.xsl
```

### Internal => Legacy eZXML

Conversion from the internal format to the Legacy eZXML format is performed by a `Xslt` converter configured with a stylesheets found in:

```
eZ/Publish/Core/FieldType/RichText/Resources/stylesheets/ezxml/docbook
```

### Legacy eZXML => Internal

Conversion from Legacy eZXML to the internal format is performed by `ToRichTextPreNormalize` and `Xslt` converters configures with main `Aggregate` converter to act as one:

 1. `eZ/Publish/Core/FieldType/XmlText/Converter/ToRichTextPreNormalize` converter

   With Legacy XmlText it is possible to store XML data differently in regard to the usage of temporary paragraphs that wrap block level elements. In Legacy Stack that is of no consequence as the difference is always normalized, but that also needs to be handled in the new stack. In XmlText field type implementation in the new stack that is handled by `Expanding` and `EmbedLinking` converters.

   In order to have a consistent format to convert from, `ToRichTextPreNormalize` simply aggregates these two converters and applies them in succession.

   This converter should always execute first, in order to normalize the format for the subsequent converters.

 2. `Xslt` converter - this is the *main* converter, configured with a stylesheets found in:

   ```
   eZ/Publish/Core/FieldType/RichText/Resources/stylesheets/ezxml/docbook
   ```

## Normalization

Normalizers of XML string input are implemented in order to relax requirements for the client side. Currently only `DocumentTypeDefinition` normalizer is implemented. It is used to normalize XML string coming from the editor, which misses DTD defining HTML entities. See:

 - `eZ/Publish/Core/FieldType/RichText/Normalizer`
 - `eZ/Publish/Core/FieldType/RichText/Normalizer/Aggregate`
 - `eZ/Publish/Core/FieldType/RichText/Normalizer/DocumentTypeDefinition`

## External storage

Sole purpose of external storage is to handle URLs of the external type. URLs need to be handled when storing and reading field data:

 - storing: gets external URLs from the document and stores them to the database, then replaces them in the document with internal links, pointing to the stored external URLs.
 - reading: replaces internal links in the document with external URLs read from the database.

External storage data structures are shared with `Url` field type. For this reason Legacy Storage gateway extends the one from the `Url` field type.

## Test setup

Even though some conversions use some special converters, main conversion will always be implemented through XSLT transformation, using `Xslt` converter. Therefore conversion between the formats is tested through the `Xslt` converter. The fixtures for these tests can be used as a documentation for both the involved formats and conversion between them. You can find them here:

```
eZ/Publish/Core/FieldType/Tests/RichText/Converter/Xslt/_fixtures
```

Same set of fixtures exists for each format, but in separate subdirectories. Important thing to note is that conversion is always done between the internal format and some other format, meaning internal format is always involved in conversion. When testing a particular conversion, a fixture filename from the source format will be matched with the fixture filename in the destination format.

In some cases the conversion **from** the internal format will not be reversible, meaning that the reverse conversion will not produce the same result. In this case the fixture in the destination format will include string `lossy`, indicating that the conversion results in loss of information. Note that this will never be the case with internal format, as that one is the referent format. When converting **to** the internal format, fixtures containing string `lossy` will be skipped, as the result would obviously not match the target fixture.

"Lossy" fixtures should always contain the explanation of the information loss in the comment after the XML prolog. Also, if needed, lossy conversion will be further tested with fixtures in the `lossy` subdirectory. In this case fixtures will be matched with corresponding fixtures in the internal format, in the same directory.

Note that `lossy` subdirectory will also sometimes contain fixtures for conversions that is not reversible by design (and not by limitation), like embedding of content.
