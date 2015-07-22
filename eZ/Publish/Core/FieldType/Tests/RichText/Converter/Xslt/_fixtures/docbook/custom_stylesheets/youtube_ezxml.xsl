<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
    xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"
    exclude-result-prefixes="ezcustom"
    version="1.0">

  <xsl:template match="ezcustom:youtube">
    <xsl:element name="custom">
      <xsl:attribute name="name">youtube</xsl:attribute>
      <xsl:for-each select="@ezcustom:*">
        <xsl:attribute name="custom:{local-name()}">
          <xsl:value-of select="current()"/>
        </xsl:attribute>
      </xsl:for-each>
    </xsl:element>
  </xsl:template>

</xsl:stylesheet>
