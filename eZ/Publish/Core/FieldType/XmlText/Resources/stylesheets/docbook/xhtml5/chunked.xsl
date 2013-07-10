<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:docbook="http://docbook.org/ns/docbook"
    exclude-result-prefixes="docbook"
    version="1.0">
  <xsl:import href="core.xsl"/>
  <xsl:output indent="yes" omit-xml-declaration="yes" encoding="UTF-8"/>

  <xsl:template match="/docbook:article">
    <xsl:apply-templates/>
  </xsl:template>
</xsl:stylesheet>
