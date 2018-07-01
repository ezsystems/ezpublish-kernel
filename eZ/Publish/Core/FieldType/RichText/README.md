# RichText field type for eZ Platform

This is the RichText field type for eZ Platform, it's a field type for supporting
rich formatted text stored in a structured xml format.

This field type succeeds the former [XMLText](https://github.com/ezsystems/ezplatform-xmltext-fieldtype)
field type found in eZ Publish 5.x and before.

### Background

When looking to find a structured text format for eZ Platform, we wanted to pick something that
was wildly used in the industry, and which could support the custom & embed structures we have
had in eZ Publish for years which has enabled us to seamless target several channels / formats
based on same internal stored formats.

What we had at the time was inspired by early drafts of XHTML 2.0, a standard made for the most
part obsolete by html5.

We also knew from experience we had to support html5 as an input/output format for RichText editors
to reduce the number of customizations we had to apply on top of available editors. Which would make
it hard to keep up to date, and forces us to deal with edge cases ourselves instead of relying on
the editor doing it for us.

In RichText we have ended up with a solution that is built on a more widly used internal format, 
moved closer to html5 supported by editores, and better suited to support wider range of formats.

# Format

### Storage format

Storage format in RichText is [docbook](http://docbook.org/), for further info on it's schema, and how we
extend it with RELAX NG, see [Resources/schemas/docbook/](Resources/schemas/docbook).

### Input/Output formats

This field type support several output and input formats, docbook, ezxml _(legacy format)_, and
two forms of xhtml5 _(edit and output)_.

Further reading on these formats and how they uses schemas, xslt and dtd, see [Resources/](Resources).


# Migrating

The architecture allows for migration to and from several formats in the future, currently the following is the main one supported:

### From eZ Publish

For migrating from ez Publish's XMLText format, have a look at the seperate [XMLText field type](https://github.com/ezsystems/ezplatform-xmltext-fieldtype).
