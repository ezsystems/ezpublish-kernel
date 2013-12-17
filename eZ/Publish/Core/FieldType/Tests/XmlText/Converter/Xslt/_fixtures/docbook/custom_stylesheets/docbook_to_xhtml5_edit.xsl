<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom"
    exclude-result-prefixes="ezcustom"
    version="1.0">

  <xsl:variable name="outputNamespace" select="''"/>

  <xsl:template match="ezcustom:custom[@ezcustom:name='youtube']">
    <xsl:element name="youtube" namespace="{$outputNamespace}">
      <xsl:attribute name="width"><xsl:value-of select="@ezcustom:videoWidth"/></xsl:attribute>
      <xsl:attribute name="height"><xsl:value-of select="@ezcustom:videoHeight"/></xsl:attribute>
      <xsl:attribute name="src"><xsl:value-of select="@ezcustom:video"/></xsl:attribute>
      <xsl:value-of select="@ezcustom:video"/>
    </xsl:element>
  </xsl:template>

</xsl:stylesheet>
