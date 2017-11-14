================================================
Schemas for eZ Publish variant of Docbook format
================================================

Docbook normative
=================

Version of Docbook format used as a base for eZ Publish variant is 5.0.
Schemas for this version can be obtained here: http://docbook.org/xml/5.0/

RELAX NG schema is normative, and it is preferred over the other schema languages.
It is also recommended to validate documents using Schematron rules.

Both are provided here unmodified:

 - docbook.rng
 - docbook.sch

eZ Publish Docbook variant
==========================

Version identifier for current eZ Publish Docbook variant is ``5.0-variant ezpublish-1.0``.
Shema used for eZ Publish variant of Docbook format uses unmodified Docbook
RELAX NG schema as a base for replacement and extension:

 - ezpublish.rng

Similarly, Schematron rules for eZ Publish variant of Docbook format are provided in separate
file:

 - docbook.sch

As Schematron rules from Docbook 5.0 release are in old Schematron 1.5 format
which is not handled, this file is converted to new ISO Schematron:

 - docbook.iso.sch

ISO Schematron is handled through XSLT transformation, so XSLT stylesheet
generated from ISO Schematron is also provided:

 - docbook.iso.sch.xsl

To generate XSLT stylesheet from a Schematron file, `xsltproc` - command line XSLT processor, needs
to be installed on the system. XSLT stylesheet can be generated issuing the following command:

.. code:: bash

   # Usage: sch2xsl.sh source_file target_file
   ../../stylesheets/schematron/sch2xsl.sh docbook.iso.sch docbook.iso.sch.xsl
