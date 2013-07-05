<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ezxhtml5="http://ez.no/namespaces/ezpublish5/xhtml5"
    exclude-result-prefixes="ezxhtml5"
    version="1.0">
  <xsl:output indent="yes" encoding="UTF-8"/>
  <xsl:strip-space elements="*"/>

  <xsl:template match="ezxhtml5:article">
    <article xmlns="http://docbook.org/ns/docbook" version="5.0">
      <xsl:apply-templates/>
    </article>
  </xsl:template>

  <xsl:template match="ezxhtml5:section">
    <xsl:element name="section" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:p">
    <xsl:element name="para" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:em">
    <xsl:element name="emphasis" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:strong">
    <xsl:element name="emphasis" namespace="http://docbook.org/ns/docbook">
      <xsl:attribute name="role">strong</xsl:attribute>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="ezxhtml5:*['hx'=translate(local-name(), '123456', 'xxxxxx')]">
    <xsl:element name="title" namespace="http://docbook.org/ns/docbook">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>
</xsl:stylesheet>
