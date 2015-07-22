<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ezxhtml5="http://ez.no/namespaces/ezpublish5/xhtml5"
    version="1.0">

  <xsl:output omit-xml-declaration="yes" indent="yes" encoding="UTF-8"/>

  <xsl:template match="/ezxhtml5:section">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="*">
    <xsl:element name="{local-name()}">
      <xsl:apply-templates select="@*|*|text()"/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="@*|text()">
    <xsl:copy/>
  </xsl:template>

</xsl:stylesheet>
