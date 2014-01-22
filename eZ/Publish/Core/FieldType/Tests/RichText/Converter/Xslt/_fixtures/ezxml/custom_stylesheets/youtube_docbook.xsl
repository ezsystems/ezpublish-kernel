<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"
    xmlns="http://docbook.org/ns/docbook"
    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
    version="1.0">

  <xsl:output indent="yes" encoding="UTF-8"/>

  <xsl:template match="custom[@name='youtube']">
    <xsl:element name="ezcustom:youtube">
      <xsl:attribute name="ezcustom:videoWidth">
        <xsl:value-of select="@custom:videoWidth"/>
      </xsl:attribute>
      <xsl:attribute name="ezcustom:videoHeight">
        <xsl:value-of select="@custom:videoHeight"/>
      </xsl:attribute>
      <xsl:attribute name="ezcustom:video">
        <xsl:value-of select="@custom:video"/>
      </xsl:attribute>
    </xsl:element>
  </xsl:template>

</xsl:stylesheet>
