<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ezxhtml5="http://ez.no/namespaces/ezpublish5/xhtml5"
    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
    exclude-result-prefixes="ezxhtml5"
    version="1.0">

  <xsl:output indent="yes" encoding="UTF-8"/>

  <xsl:template match="ezxhtml5:youtube">
    <ezcustom:custom>
      <xsl:attribute name="ezcustom:name">
        <xsl:value-of select="'youtube'"/>
      </xsl:attribute>
      <xsl:attribute name="ezcustom:videoWidth">
        <xsl:value-of select="@width"/>
      </xsl:attribute>
      <xsl:attribute name="ezcustom:videoHeight">
        <xsl:value-of select="@height"/>
      </xsl:attribute>
      <xsl:attribute name="ezcustom:video">
        <xsl:value-of select="@src"/>
      </xsl:attribute>
    </ezcustom:custom>
  </xsl:template>

</xsl:stylesheet>
