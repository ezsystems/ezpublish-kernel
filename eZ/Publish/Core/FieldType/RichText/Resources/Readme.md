# Resources overview

Resources for the different formats for RichText.

RichText storage format is docbook (5), two other formats are supported for transformations to/from docbook:
- ezxml, _for input/output, however only main use case is [upgrade from eZ Publish](https://doc.ez.no/display/DEVELOPER/Upgrading+from+5.4.x+and+2014.11+to+16.xx)._
- ezhtml5, _exists in two different representations:_
  - "edit" a semantic version for UI use, for input/output.
  - "output" for web view, for output only.

## Folder structure

### DTD
DTD file for html character entities used in ezxhtml5 format.


### Schemas
Schema files that exists:
- docbook _(see schemas/docbook/Readme.rst for further details)_
- ezxhtml5 "output" _(based on W3C xhtml5 standard, with some tags removed as described in changes.rst)_
  - Note: "edit" format is more complex for UI needs, and will need to be described in RelaxNG and/or Schematron.
- ezxml _(schema based on the format for eZ Publish XmlText Field Type storage format)_


### Stylesheets
This is where the logic around each format is defined, this is where a given tag gets a meaning by being handled or
not for transformation to and from the internal docbook format.

The folder structure is modeled in the following way to define to/from, the first folder level denotes format _from_.

Examples:
- `xhtml/edit/docbook.xsl` defines transformation from ezxhtml5 "edit" to docbook.
- `docbook/xhtml5/edit/*.xsl` defines transformation files from docbook to ezxhtml5 "edit".

Note: _Transformation from docbook to xhtml5 formats typically have several xsl files as there are logic done by
converters as well during the process to enrich the data._
