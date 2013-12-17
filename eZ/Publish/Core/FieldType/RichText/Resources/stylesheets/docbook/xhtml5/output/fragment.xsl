<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:docbook="http://docbook.org/ns/docbook"
    exclude-result-prefixes="docbook"
    version="1.0">

  <!-- XSL stylesheets are dynamically added to this one via <xsl:import/> -->
  <!-- See eZ\Publish\Core\FieldType\RichText\Converter\Xslt::getXSLTProcessor() -->
  <!--<xsl:import href="core.xsl"/>-->

  <xsl:output omit-xml-declaration="yes" indent="yes" encoding="UTF-8"/>

  <xsl:template match="/docbook:section">
    <xsl:apply-templates/>
  </xsl:template>

</xsl:stylesheet>
