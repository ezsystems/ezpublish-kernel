<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:docbook="http://docbook.org/ns/docbook"
    exclude-result-prefixes="docbook"
    version="1.0">

  <!-- XSL stylesheets are dynamically added to this one via <xsl:import/> -->
  <!-- See eZ\Publish\Core\FieldType\RichText\Converter\Xslt::getXSLTProcessor() -->
  <!--<xsl:import href="core.xsl"/>-->

  <xsl:output indent="yes" encoding="UTF-8"/>

  <xsl:variable name="outputNamespace" select="'http://ez.no/namespaces/ezpublish5/xhtml5'"/>
  <xsl:template match="/docbook:section">
    <xsl:element name="section" namespace="{$outputNamespace}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

</xsl:stylesheet>
